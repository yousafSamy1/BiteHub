<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderInvoiceMail;
use Carbon\Carbon;

class OrderSettlementService
{
    /**
     * Process financial settlement for a delivered order.
     */
    public function settle(Order $order, $otpValid, array $inputs = [], $isVerifiedMatch = false)
    {
        if ($order->OrderStatus === 'Delivered') {
            return ['status' => 'info', 'message' => 'Order already delivered.'];
        }

        if (!$otpValid) {
            return ['status' => 'error', 'message' => 'Invalid delivery code provided.'];
        }

        $order->load(['payment', 'customer.user']);
        $paymentMethod = $order->payment->Method ?? 'Cash';
        $isOnline = in_array($paymentMethod, ['Card', 'Wallet', 'Online', 'Online Payment']);
        
        // Final condition for digital wallet movement
        $shouldSettleWallets = $isOnline || $isVerifiedMatch;

        $agentFee = 15.00;
        $commissionRate = 0.15; // 15% site commission
        $vendorRate     = 0.85; // 85% vendor share

        DB::transaction(function() use (
            $order, $inputs, $isOnline, $agentFee, $commissionRate, $vendorRate, $shouldSettleWallets, &$msg
        ) {
            $agentUser = $order->deliveryAgent ? $order->deliveryAgent->user : null;
            $owner = User::where('Role', 'Admin')->first();
            
            // Identify Vendor
            $vendorUser = null;
            if ($order->KitchenOwnerID) {
                $vendorUser = \App\Models\KitchenOwner::find($order->KitchenOwnerID)->user ?? null;
            } elseif ($order->CatererID) {
                $vendorUser = \App\Models\Caterer::find($order->CatererID)->user ?? null;
            }

            if ($order->OrderType === 'Meal Plan' || $order->SubscriptionID) {
                // ── Meal Plan Logic ──────────────────────────────────
                $subscription = $order->subscription;
                $cashPaid = (float) ($inputs['plan_cash_paid'] ?? 0);
                $walletChange = (float) ($inputs['wallet_change'] ?? 0);

                if ($cashPaid > 0 || $walletChange > 0) {
                    $itemsPrice = max(0, $cashPaid - $agentFee);
                    $vendorShare = round($itemsPrice * $vendorRate, 2);
                    $siteShare = round($itemsPrice * $commissionRate, 2);

                    if ($shouldSettleWallets) {
                        if ($agentUser) {
                            $totalAgentDebt = $itemsPrice + $walletChange;
                            $agentUser->increment('cash_to_settle', $totalAgentDebt);
                        }

                        if ($walletChange > 0 && $order->customer) {
                            $order->customer->increment('WalletBalance', $walletChange);
                        }

                        if ($vendorUser) $vendorUser->increment('Wallet_balance', $vendorShare);
                        if ($owner) $owner->increment('Wallet_balance', $siteShare);
                    }
                    $msg = "Plan meal delivered!" . ($shouldSettleWallets ? " Items (" . $itemsPrice . ") + Wallet (" . $walletChange . ") settled." : " (Manual marked, wallet skipped)");
                } else {
                    if ($subscription) {
                        $totalMeals = (max(1, (int)$subscription->DurationDays)) * (max(1, (int)$subscription->MealsPerDay));
                        $mealPrice = $subscription->Price / $totalMeals;
                        
                        $vendorShare = round($mealPrice * $vendorRate, 2);
                        $siteShare = round($mealPrice * $commissionRate, 2);

                        if ($shouldSettleWallets) {
                            if ($vendorUser) $vendorUser->increment('Wallet_balance', $vendorShare);
                            if ($owner) $owner->increment('Wallet_balance', $siteShare);
                        }
                    }
                    if ($shouldSettleWallets && $agentUser) {
                        $agentUser->increment('Wallet_balance', $agentFee);
                    }
                    $msg = "Plan meal (Prepaid) delivered!" . ($shouldSettleWallets ? "" : " (Manual marked)");
                }
            } else {
                // ── Standard Order Logic ─────────────────────────────
                $orderTotal = (float) $order->TotalPrice;
                $itemsPrice = max(0, $orderTotal - $agentFee);
                $vendorShare = round($itemsPrice * $vendorRate, 2);
                $siteShare = round($itemsPrice * $commissionRate, 2);
                $walletChange = (float) ($inputs['wallet_change'] ?? 0);

                if ($isOnline) {
                    // Vendor was already credited upon payment. Platform share is 0%.
                    // We only credit the delivery agent now, moving the transit fee from Owner to Agent.
                    if ($agentUser) {
                        $agentUser->increment('Wallet_balance', $agentFee);
                        $owner = User::where('Role', 'Admin')->first();
                        if ($owner) $owner->decrement('Wallet_balance', $agentFee);
                    }
                    $msg = "Order delivered! Delivery fee credited to agent.";
                } else {
                    if ($shouldSettleWallets) {
                        if ($agentUser) {
                            $pointsDiscount = (float) ($order->PointsDiscount ?? 0);
                            $promoDiscount  = (float) ($order->PromoDiscount ?? 0);
                            $totalAgentDebt = max(0, $itemsPrice - $pointsDiscount - $promoDiscount) + $walletChange;
                            $agentUser->increment('cash_to_settle', $totalAgentDebt);

                            // SYSTEM PAYS FOR POINTS: Deduct discount from Platform Profit
                            if ($pointsDiscount > 0) {
                                $owner = User::where('Role', 'Admin')->first();
                                if ($owner) $owner->decrement('Wallet_balance', $pointsDiscount);
                            }
                            // Deduct PROMO discount from appropriate party
                            if ($promoDiscount > 0) {
                                $promoOwner = null;
                                if (!empty($order->PromoCode)) {
                                    $pCode = \App\Models\PromoCode::where('Code', $order->PromoCode)->first();
                                    if ($pCode) {
                                        $promoOwner = $pCode->CreatorRole; // 'Admin', 'KitchenOwner', 'Caterer'
                                    }
                                }
                                
                                if ($promoOwner === 'KitchenOwner' || $promoOwner === 'Caterer') {
                                    // VENDOR PAYS FOR PROMO
                                    if ($vendorUser) $vendorUser->decrement('Wallet_balance', $promoDiscount);
                                } else {
                                    // SYSTEM PAYS FOR PROMO
                                    $owner = User::where('Role', 'Admin')->first();
                                    if ($owner) $owner->decrement('Wallet_balance', $promoDiscount);
                                }
                            }
                        }
                        
                        if ($walletChange > 0 && $order->customer) {
                            $order->customer->increment('WalletBalance', $walletChange);
                        }
                        if ($vendorUser) $vendorUser->increment('Wallet_balance', $vendorShare);
                        if ($owner) $owner->increment('Wallet_balance', $siteShare);
                        $msg = "Cash order completed! Financials settled.";
                    } else {
                        $msg = "Cash order marked delivered. Wallet updates skipped (No OTP verification).";
                    }
                }
            }

            $order->update(['OrderStatus' => 'Delivered']);

            // Daily Bonus (10+ Orders) - Only for Agents
            if ($order->deliveryAgent) {
                $todayCompleted = Order::where('DeliveryAgentID', $order->DeliveryAgentID)
                    ->where('OrderStatus', 'Delivered')
                    ->whereDate('CreatedAt', Carbon::today())
                    ->count();
                
                if ($todayCompleted == 11 && $agentUser) {
                    $agentUser->increment('Wallet_balance', 50.00);
                    $msg .= " 🎉 Agent earned a 50 EGP Completion Bonus!";
                }
            }

            // Loyalty Points
            if (!$order->PointsAwarded && $order->LoyaltyPoints > 0) {
                $customer = $order->customer;
                if ($customer) {
                    LoyaltyTransaction::create([
                        'CustomerID'  => $customer->CustomerID,
                        'Points'      => $order->LoyaltyPoints,
                        'Type'        => 'Earned',
                        'Description' => 'Order #' . $order->OrderID . ' delivered — earned ' . $order->LoyaltyPoints . ' BitePoints 🎉',
                    ]);
                    $order->update(['PointsAwarded' => true]);
                }
            }

            // Email Invoice
            try {
                if ($order->customer && $order->customer->user) {
                    Mail::to($order->customer->user->Email)->send(new OrderInvoiceMail($order));
                }
            } catch (\Exception $e) {
                Log::warning('Invoice failed: ' . $e->getMessage());
            }
        });

        return ['status' => 'success', 'message' => $msg];
    }
}
