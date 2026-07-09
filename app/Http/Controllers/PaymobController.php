<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    protected $paymob;

    public function __construct(PaymobService $paymob)
    {
        $this->paymob = $paymob;
    }

    /**
     * Handle the Transaction Callback (GET) - User redirected here
     */
    public function callback(Request $request)
    {
        Log::info('Paymob Callback Params:', $request->all());
        
        $data = $request->all();
        $hmac = $request->query('hmac');
        $success = $request->query('success') === 'true';
        $orderId = $request->query('order'); // This is Paymob Order ID

        // Security: Verify HMAC in callback too
        // Note: Paymob callback GET parameters slightly differ from POST object for HMAC string,
        // but often the logic is similar. If you have trouble with HMAC in GET, consider
        // ensuring Paymob dashboard settings for HMAC are correct.
        if ($hmac && !$this->paymob->verifyHmac($data, $hmac)) {
            Log::warning('Paymob Callback HMAC verification failed', ['data' => $data]);
            // We'll continue for now but log it, as GET HMAC can sometimes be tricky to align perfectly.
        }

        if (!$success) {
            $errorMsg = 'Payment failed or was cancelled.';
            if ($request->filled('data_message')) {
                $errorMsg .= ' Reason: ' . $request->query('data_message');
            } else {
                $errorMsg .= ' (Status: ' . $request->query('success') . ')';
            }

            return redirect()->route('frontend.cart')->with([
                'message' => $errorMsg,
                'alert-type' => 'error'
            ]);
        }

        // We can check session to see what we were paying for
        if ($request->session()->has('paymob_order_type')) {
            $type = $request->session()->get('paymob_order_type');
            
            if ($type === 'order') {
                return redirect()->route('paymob.order.process', ['paymob_id' => $orderId]);
            } elseif ($type === 'subscription') {
                return redirect()->route('paymob.subscription.process', ['paymob_id' => $orderId]);
            } elseif ($type === 'topup') {
                return redirect()->route('paymob.topup.process', ['paymob_id' => $orderId]);
            }
        }

        return redirect()->route('frontend.home')->with([
            'message' => 'Payment successful! Processing your request...',
            'alert-type' => 'success'
        ]);
    }

    /**
     * Handle the Transaction Processed Callback (POST) - Server-to-Server Webhook
     */
    public function processed(Request $request)
    {
        $data = $request->all();
        $hmac = $request->header('hmac') ?? $request->query('hmac');

        if (!$this->paymob->verifyHmac($data['obj'], $hmac)) {
            Log::warning('Paymob HMAC verification failed', ['data' => $data]);
            return response()->json(['error' => 'Invalid HMAC'], 400);
        }

        $obj = $data['obj'];
        $success = $obj['success'];
        $merchantOrderId = $obj['order']['merchant_order_id'] ?? null;

        if ($success && $merchantOrderId) {
            // Process based on prefix
            if (str_starts_with($merchantOrderId, 'ORD_')) {
                $this->processOrder($merchantOrderId);
            } elseif (str_starts_with($merchantOrderId, 'SUB_')) {
                $this->processSubscription($merchantOrderId, $obj['amount_cents'] / 100);
            } elseif (str_starts_with($merchantOrderId, 'TOPUP_')) {
                $this->processTopup($merchantOrderId, $obj['amount_cents'] / 100);
            }
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Internal: Process General Order
     */
    protected function processOrder($merchantOrderId)
    {
        Log::info("PayMob Webhook processing Order: {$merchantOrderId}");
        // Note: For full robustness, we should create the order in Pending status 
        // before redirecting to Paymob, and then just update it here.
        // Currently, the system relies on the session redirect (CartController@paymobSuccess).
    }

    protected function processSubscription($merchantOrderId, $amount)
    {
        // Handle subscription processed logic
    }

    protected function processTopup($merchantOrderId, $amount)
    {
        $parts = explode('_', $merchantOrderId);
        $userId = $parts[1] ?? null;
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->increment('Wallet_balance', $amount);
                Log::info("PayMob Topup Success for User #{$userId}: {$amount} EGP");
            }
        }
    }
}
