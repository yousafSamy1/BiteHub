<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\SavedPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Submit a new withdrawal request
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50',
            'method_id' => 'required|exists:saved_payment_methods,id',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        // Restriction: Block withdrawal if agent has cash debt
        if (($user->cash_to_settle ?? 0) > 0) {
            return back()->with([
                'message' => 'You cannot withdraw funds while you have outstanding cash collections (' . number_format($user->cash_to_settle, 2) . ' EGP). Please settle your debt first.', 
                'alert-type' => 'error'
            ]);
        }

        if ($user->Wallet_balance < $amount) {
            return back()->with(['message' => 'Insufficient wallet balance.', 'alert-type' => 'error']);
        }

        $method = SavedPaymentMethod::where('id', $request->method_id)
            ->where('UserID', $user->UserID)
            ->firstOrFail();

        try {
            DB::beginTransaction();
            
            $commission = $amount * 0.01;
            $netAmount = $amount - $commission;

            // Create Request
            WithdrawalRequest::create([
                'UserID' => $user->UserID,
                'Amount' => $amount,
                'Commission' => $commission,
                'NetAmount' => $netAmount,
                'Method' => $method->Type,
                'MethodDetails' => $method->Details,
                'Status' => 'Pending',
            ]);

            // Deduct from wallet immediately
            $user->decrement('Wallet_balance', $amount);

            DB::commit();

            return back()->with(['message' => 'Withdrawal request submitted successfully.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Error submitting request: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Get saved methods for the modal
     */
    public function getMethods()
    {
        $methods = SavedPaymentMethod::where('UserID', Auth::user()->UserID)->get();
        return response()->json($methods);
    }

    /**
     * Manage Saved Payment Methods (Add/Edit/Delete)
     */
    public function index()
    {
        $methods = SavedPaymentMethod::where('UserID', Auth::user()->UserID)->get();
        $requests = WithdrawalRequest::where('UserID', Auth::user()->UserID)
            ->orderByDesc('created_at')
            ->get();
            
        // Ensure legacy records have calculated values for display
        $requests->transform(function ($wr) {
            if ($wr->Commission <= 0) {
                $wr->Commission = $wr->Amount * 0.01;
                $wr->NetAmount = $wr->Amount - $wr->Commission;
            }
            return $wr;
        });
            
        return view('admin.common.payment_methods', compact('methods', 'requests'));
    }

    public function storeMethod(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Bank,VodafoneCash,InstaPay',
            'details' => 'required|array',
        ]);

        SavedPaymentMethod::create([
            'UserID' => Auth::user()->UserID,
            'Type' => $request->type,
            'Details' => $request->details,
            'IsPrimary' => $request->has('is_primary'),
        ]);

        return back()->with(['message' => 'Payment method added.', 'alert-type' => 'success']);
    }

    public function deleteMethod($id)
    {
        SavedPaymentMethod::where('id', $id)->where('UserID', Auth::user()->UserID)->firstOrFail()->delete();
        return back()->with(['message' => 'Payment method deleted.', 'alert-type' => 'success']);
    }

    /**
     * Admin: List all requests
     */
    public function adminIndex(Request $request)
    {
        $query = WithdrawalRequest::with('user');
        if ($request->status) $query->where('Status', $request->status);
        $requests = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Ensure legacy records have calculated values for display
        $requests->getCollection()->transform(function ($wr) {
            if ($wr->Commission <= 0) {
                $wr->Commission = $wr->Amount * 0.01;
                $wr->NetAmount = $wr->Amount - $wr->Commission;
            }
            return $wr;
        });

        // Calculate metrics for the admin dashboard
        $ownerBalance = User::where('Role', 'Owner')->value('Wallet_balance') ?? 0;
        $totalPending = WithdrawalRequest::where('Status', 'Pending')->sum('Amount');

        return view('admin.withdrawals.index', compact('requests', 'ownerBalance', 'totalPending'));
    }

    /**
     * Admin: Approve or Reject
     */
    public function adminUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
        ]);

        $wr = WithdrawalRequest::findOrFail($id);
        
        if ($wr->Status !== 'Pending') {
            return back()->with(['message' => 'Request already processed.', 'alert-type' => 'warning']);
        }

        try {
            DB::beginTransaction();

            $wr->Status = $request->status;
            $wr->AdminNotes = $request->notes;

            // Recalculate for legacy records if missing
            if ($wr->Commission <= 0) {
                $wr->Commission = $wr->Amount * 0.01;
                $wr->NetAmount = $wr->Amount - $wr->Commission;
            }

            $wr->save();

            // If approved, credit commission to owner
            if ($request->status === 'Approved') {
                $owner = User::where('Role', 'Owner')->first();
                if ($owner) {
                    $owner->increment('Wallet_balance', $wr->Commission);
                }
            }

            // If rejected, refund the wallet (full amount)
            if ($request->status === 'Rejected') {
                $user = $wr->user;
                $user->increment('Wallet_balance', $wr->Amount);
            }

            DB::commit();

            return back()->with(['message' => 'Withdrawal request ' . strtolower($request->status) . '.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Error updating request: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }
}
