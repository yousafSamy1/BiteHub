<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\KitchenOwner;
use App\Models\DeliveryAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SettlementSimulationTest extends TestCase
{
    /**
     * Test all settlement scenarios in a single simulation.
     */
    public function test_financial_settlements()
    {
        DB::beginTransaction();

        // 1. Setup Mock Entities
        $owner = User::create(['FullName' => 'Owner', 'Role' => 'Owner', 'Email' => 'owner@test.com', 'Password' => '123', 'Username' => 'owner']);
        
        $agentUser = User::create(['FullName' => 'Agent', 'Role' => 'Agent', 'Email' => 'agent@test.com', 'Password' => '123', 'Username' => 'agent']);
        $agent = DeliveryAgent::create(['UserID' => $agentUser->UserID, 'Status' => 'Available']);
        
        $kitchenUser = User::create(['FullName' => 'Kitchen', 'Role' => 'Kitchen', 'Email' => 'kitchen@test.com', 'Password' => '123', 'Username' => 'kitchen']);
        $kitchen = KitchenOwner::create(['UserID' => $kitchenUser->UserID, 'KitchenName' => 'Test Kitchen']);
        
        $customerUser = User::create(['FullName' => 'Customer', 'Role' => 'Customer', 'Email' => 'cust@test.com', 'Password' => '123', 'Username' => 'cust']);
        $customer = Customer::create(['UserID' => $customerUser->UserID, 'WalletBalance' => 0]);

        Auth::login($agentUser);

        echo "\n--- STARTING SETTLEMENT SIMULATION ---\n";

        // ─────────────────────────────────────────────────────────────────────
        // SCENARIO 1: Standard Online Order (Prepaid)
        // Order Total: 115 EGP (100 items + 15 delivery)
        // Expected: Vendor += 85, Owner += 15, Agent += 15
        echo "1. Standard Online Order (115 EGP)...\n";
        $order1 = Order::create([
            'CustomerID' => $customer->CustomerID,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'DeliveryAgentID' => $agent->DeliveryAgentID,
            'TotalPrice' => 115.00,
            'OrderStatus' => 'Delivering',
            'OrderType' => 'Order',
            'DeliveryCode' => '1234'
        ]);
        $pay1 = Payment::create(['Method' => 'Wallet', 'Amount' => 115.00, 'Status' => 'Completed']);
        $order1->update(['PaymentID' => $pay1->PaymentID]);

        $this->callUpdateStatus($order1->OrderID, 'Delivered', '1234');

        $this->assertEquals(85.00, $kitchenUser->fresh()->Wallet_balance); // 100 * 0.85
        $this->assertEquals(15.00, $owner->fresh()->Wallet_balance);       // 100 * 0.15
        $this->assertEquals(15.00, $agentUser->fresh()->Wallet_balance);     // fixed fee
        $this->assertEquals(0, $agentUser->fresh()->cash_to_settle);
        echo "   ✓ Success: Vendor:+85, Site:+15, Agent:+15 profit.\n";

        // Reset balances for next test
        $this->resetBalances($kitchenUser, $owner, $agentUser, $customerUser);

        // ─────────────────────────────────────────────────────────────────────
        // SCENARIO 2: Standard Cash Order with Wallet Change
        // Order Total: 115 EGP. Agent collects 140 EGP (125 items? No, Total is 115).
        // Let's say Total is 115. Agent adds 25 to customer wallet.
        // Expected: Items=100. Vendor += 85, Owner += 15. Agent debt += (100 + 25) = 125. Customer Wallet += 25.
        echo "2. Standard Cash Order (115 EGP) + 25 EGP Wallet Change...\n";
        $order2 = Order::create([
            'CustomerID' => $customer->CustomerID,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'DeliveryAgentID' => $agent->DeliveryAgentID,
            'TotalPrice' => 115.00,
            'OrderStatus' => 'Delivering',
            'OrderType' => 'Order',
            'DeliveryCode' => '5678'
        ]);
        $pay2 = Payment::create(['Method' => 'Cash', 'Amount' => 0, 'Status' => 'Pending']);
        $order2->update(['PaymentID' => $pay2->PaymentID]);

        $this->callUpdateStatus($order2->OrderID, 'Delivered', '5678', ['wallet_change' => 25]);

        $this->assertEquals(85.00, $kitchenUser->fresh()->Wallet_balance);
        $this->assertEquals(15.00, $owner->fresh()->Wallet_balance);
        $this->assertEquals(125.00, $agentUser->fresh()->cash_to_settle); // 100 items + 25 wallet change
        $this->assertEquals(25.00, $customer->fresh()->WalletBalance);
        echo "   ✓ Success: Vendor:+85, Site:+15, Agent:+125 debt, Customer:+25.\n";

        $this->resetBalances($kitchenUser, $owner, $agentUser, $customerUser);

        // ─────────────────────────────────────────────────────────────────────
        // SCENARIO 3: Meal Plan Prepaid (1-Meal Split)
        // Sub: 1000 EGP for 20 Meals (50 per meal).
        // Expected: Vendor += 42.50 (50*0.85), Owner += 7.50 (50*0.15), Agent += 15 profit.
        echo "3. Prepaid Meal Plan (50 EGP share)...\n";
        $sub = Subscription::create([
            'CustomerID' => $customer->CustomerID,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'Price' => 1000.00,
            'DurationDays' => 10,
            'MealsPerDay' => 2,
            'Status' => 'Active'
        ]);
        $order3 = Order::create([
            'CustomerID' => $customer->CustomerID,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'DeliveryAgentID' => $agent->DeliveryAgentID,
            'TotalPrice' => 0,
            'OrderType' => 'Meal Plan',
            'SubscriptionID' => $sub->SubscriptionID,
            'DeliveryCode' => '9999'
        ]);
        // Prepaid order has Online payment
        $order3->update(['PaymentID' => Payment::create(['Method' => 'Online'])->PaymentID]);

        $this->callUpdateStatus($order3->OrderID, 'Delivered', '9999');

        $this->assertEquals(42.50, $kitchenUser->fresh()->Wallet_balance);
        $this->assertEquals(7.50, $owner->fresh()->Wallet_balance);
        $this->assertEquals(15.00, $agentUser->fresh()->Wallet_balance);
        echo "   ✓ Success: Vendor:+42.5, Site:+7.5, Agent:+15 profit.\n";

        $this->resetBalances($kitchenUser, $owner, $agentUser, $customerUser);

        // ─────────────────────────────────────────────────────────────────────
        // SCENARIO 4: Meal Plan COD (Installment)
        // Agent collects 215 EGP.
        // Expected: Items=200. Vendor += 170 (200*0.85), Owner += 30 (200*0.15). Agent debt += 200. Sub PaidAmount += 215.
        echo "4. Meal Plan COD (215 EGP collected)...\n";
        $sub->update(['PaidAmount' => 0]);
        $order4 = Order::create([
            'CustomerID' => $customer->CustomerID,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'DeliveryAgentID' => $agent->DeliveryAgentID,
            'TotalPrice' => 0,
            'OrderType' => 'Meal Plan',
            'SubscriptionID' => $sub->SubscriptionID,
            'DeliveryCode' => '0000'
        ]);

        $this->callUpdateStatus($order4->OrderID, 'Delivered', '0000', ['plan_cash_paid' => 215]);

        $this->assertEquals(170.00, $kitchenUser->fresh()->Wallet_balance);
        $this->assertEquals(30.00, $owner->fresh()->Wallet_balance);
        $this->assertEquals(200.00, $agentUser->fresh()->cash_to_settle);
        $this->assertEquals(215.00, $sub->fresh()->PaidAmount);
        echo "   ✓ Success: Vendor:+170, Site:+30, Agent:+200 debt, Sub:+215 paid.\n";

        echo "--- SIMULATION COMPLETE: ALL SCENARIOS VERIFIED ---\n";

        DB::rollBack();
    }

    private function callUpdateStatus($id, $status, $code, $params = [])
    {
        $request = new Request(array_merge([
            'status' => $status,
            'delivery_code' => $code
        ], $params));

        $controller = new \App\Http\Controllers\AgentController();
        
        // Mock session and flash behavior by calling the method manually
        // We bypass the actual route middleware to test the logic directly
        $controller->updateDeliveryStatus($request, $id);
    }

    private function resetBalances($k, $o, $a, $c)
    {
        $k->update(['Wallet_balance' => 0]);
        $o->update(['Wallet_balance' => 0]);
        $a->update(['Wallet_balance' => 0, 'cash_to_settle' => 0]);
        $c->customer->update(['WalletBalance' => 0]);
    }
}
