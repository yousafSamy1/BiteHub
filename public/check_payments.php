<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\KitchenOwner;
use App\Models\DeliveryAgent;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function log_result($msg) {
    echo $msg . "\n";
}

DB::beginTransaction();

try {
    // 1. Setup Mock Entities
    // Clear any existing pseudo-owners to ensure we hit the one we want
    User::where('Role', 'Admin')->update(['Role' => 'Customer']); 

    $owner = User::create(['FullName' => 'Site Admin', 'Role' => 'Admin', 'Email' => 'admin_test_' . rand() . '@bitehub.com', 'Password' => bcrypt('123')]);
    
    $agentUser = User::create(['FullName' => 'Agent Test', 'Role' => 'DeliveryAgent', 'Email' => 'agent' . rand() . '@test.com', 'Password' => bcrypt('123')]);
    $agent = DeliveryAgent::create(['UserID' => $agentUser->UserID, 'Status' => 'Available']);
    
    $kitchenUser = User::create(['FullName' => 'Kitchen Test', 'Role' => 'KitchenOwner', 'Email' => 'kitchen' . rand() . '@test.com', 'Password' => bcrypt('123')]);
    $kitchen = KitchenOwner::create(['UserID' => $kitchenUser->UserID, 'KitchenName' => 'Test Kitchen']);
    
    $customerUser = User::create(['FullName' => 'Customer Test', 'Role' => 'Customer', 'Email' => 'cust' . rand() . '@test.com', 'Password' => bcrypt('123')]);
    $customer = Customer::create(['UserID' => $customerUser->UserID, 'WalletBalance' => 0]);

    Auth::login($agentUser);

    log_result("--- STARTING PAYMENT LOGIC AUDIT ---");

    $agentFee = 15.00;

    // ─────────────────────────────────────────────────────────────────────
    // SCENARIO 1: Standard Online Order
    log_result("Scenario 1: Standard Online (115 EGP)");
    $prevOwnerBal = (float)$owner->fresh()->Wallet_balance;
    $prevKitchenBal = (float)$kitchenUser->fresh()->Wallet_balance;
    $prevAgentProfit = (float)$agentUser->fresh()->Wallet_balance;

    $order1 = Order::create([
        'CustomerID' => $customer->CustomerID,
        'KitchenOwnerID' => $kitchen->KitchenOwnerID,
        'DeliveryAgentID' => $agent->DeliveryAgentID,
        'TotalPrice' => 115.00,
        'OrderStatus' => 'Delivering',
        'OrderType' => 'Order',
        'DeliveryCode' => '1111'
    ]);
    $order1->update(['PaymentID' => Payment::create(['Method' => 'Wallet', 'Amount' => 115, 'Status' => 'Completed'])->PaymentID]);

    $req1 = new Request(['status' => 'Delivered', 'delivery_code' => '1111']);
    (new \App\Http\Controllers\AgentController())->updateDeliveryStatus($req1, $order1->OrderID);

    $kDelta = (float)$kitchenUser->fresh()->Wallet_balance - $prevKitchenBal;
    $oDelta = (float)$owner->fresh()->Wallet_balance - $prevOwnerBal;
    $aDelta = (float)$agentUser->fresh()->Wallet_balance - $prevAgentProfit;
    
    log_result("   Deltas -> Kitchen: $kDelta, Owner: $oDelta, Agent: $aDelta");
    if($kDelta == 85 && $oDelta == 15 && $aDelta == 15) log_result("   PASS: Split correctly (85/15/15)"); else log_result("   FAIL");

    // ─────────────────────────────────────────────────────────────────────
    // SCENARIO 2: Standard Cash + Wallet Change
    log_result("Scenario 2: Standard Cash (115 EGP) + 25 Wallet Change");
    $prevOwnerBal = (float)$owner->fresh()->Wallet_balance;
    $prevKitchenBal = (float)$kitchenUser->fresh()->Wallet_balance;
    $prevAgentDebt = (float)$agentUser->fresh()->cash_to_settle;
    $prevCustWal = (float)$customer->fresh()->WalletBalance;

    $order2 = Order::create([
        'CustomerID' => $customer->CustomerID,
        'KitchenOwnerID' => $kitchen->KitchenOwnerID,
        'DeliveryAgentID' => $agent->DeliveryAgentID,
        'TotalPrice' => 115.00,
        'OrderStatus' => 'Delivering',
        'OrderType' => 'Order',
        'DeliveryCode' => '2222'
    ]);
    $order2->update(['PaymentID' => Payment::create(['Method' => 'Cash'])->PaymentID]);

    $req2 = new Request(['status' => 'Delivered', 'delivery_code' => '2222', 'wallet_change' => 25]);
    (new \App\Http\Controllers\AgentController())->updateDeliveryStatus($req2, $order2->OrderID);

    $kDelta = (float)$kitchenUser->fresh()->Wallet_balance - $prevKitchenBal;
    $oDelta = (float)$owner->fresh()->Wallet_balance - $prevOwnerBal;
    $aDebtDelta = (float)$agentUser->fresh()->cash_to_settle - $prevAgentDebt;
    $cDelta = (float)$customer->fresh()->WalletBalance - $prevCustWal;

    log_result("   Deltas -> Kitchen: $kDelta, Owner: $oDelta, Agent Debt: $aDebtDelta, Cust Wallet: $cDelta");
    if($kDelta == 85 && $oDelta == 15 && $aDebtDelta == 125 && $cDelta == 25) log_result("   PASS: Cash debt and wallet change correct."); else log_result("   FAIL");

    // ─────────────────────────────────────────────────────────────────────
    // SCENARIO 3: Meal Plan COD (215 EGP Paid + 10 Wallet Change)
    log_result("Scenario 3: Meal Plan COD (215 EGP paid + 10 Wallet Change)");
    $prevOwnerBal = (float)$owner->fresh()->Wallet_balance;
    $prevKitchenBal = (float)$kitchenUser->fresh()->Wallet_balance;
    $prevAgentDebt = (float)$agentUser->fresh()->cash_to_settle;

    $sub = Subscription::create(['CustomerID' => $customer->CustomerID, 'KitchenOwnerID' => $kitchen->KitchenOwnerID, 'Price' => 1000, 'DurationDays' => 10, 'MealsPerDay' => 1, 'Status' => 'Active', 'PaidAmount' => 0]);
    $order3 = Order::create([
        'CustomerID' => $customer->CustomerID,
        'KitchenOwnerID' => $kitchen->KitchenOwnerID,
        'DeliveryAgentID' => $agent->DeliveryAgentID,
        'OrderType' => 'Meal Plan',
        'SubscriptionID' => $sub->SubscriptionID,
        'DeliveryCode' => '3333',
        'OrderStatus' => 'Delivering',
        'TotalPrice' => 0
    ]);
    
    $req3 = new Request(['status' => 'Delivered', 'delivery_code' => '3333', 'plan_cash_paid' => 215, 'wallet_change' => 10]);
    (new \App\Http\Controllers\AgentController())->updateDeliveryStatus($req3, $order3->OrderID);

    $kDelta = (float)$kitchenUser->fresh()->Wallet_balance - $prevKitchenBal;
    $oDelta = (float)$owner->fresh()->Wallet_balance - $prevOwnerBal;
    $aDebtDelta = (float)$agentUser->fresh()->cash_to_settle - $prevAgentDebt; // (215-15) + 10 = 210
    $sPaidDelta = (float)$sub->fresh()->PaidAmount;

    log_result("   Deltas -> Kitchen: $kDelta, Owner: $oDelta, Agent Debt: $aDebtDelta, Sub Paid Delta: $sPaidDelta");
    if($kDelta == 170 && $oDelta == 30 && $aDebtDelta == 210 && $sPaidDelta == 215) log_result("   PASS: COD split and sub payment correct."); else log_result("   FAIL");

    log_result("--- AUDIT COMPLETE ---");

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
} finally {
    DB::rollBack();
}
