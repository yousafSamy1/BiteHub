<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\DeliveryAgent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DispatchSubscriptionOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:dispatch-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches daily orders for active meal plan subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning all active subscriptions to generate lifetime orders...");

        $activeSubscriptions = Subscription::where('Status', 'Active')
            ->with(['menuItems', 'customer'])
            ->get();

        if ($activeSubscriptions->isEmpty()) {
            $this->info("No active subscriptions found.");
            return;
        }

        $totalDispatched = 0;

        foreach ($activeSubscriptions as $sub) {
            $start = Carbon::parse($sub->StartDate);
            $end = Carbon::parse($sub->EndDate);

            // Safety limit (e.g., max 180 days to prevent infinite loops on bad data)
            $daysCount = min($start->diffInDays($end), 180);

            $timeSlots = $sub->PreferredTimes ?? [];
            if (empty($timeSlots)) {
                $timeSlots = ['09:00'];
            }

            $approvedItems = $sub->menuItems()->wherePivot('Status', 'Approved')->get();
            if ($approvedItems->isEmpty()) {
                continue;
            }

            for ($i = 0; $i <= $daysCount; $i++) {
                $date = $start->copy()->addDays($i);

                foreach ($timeSlots as $timeSlot) {
                    $slot = substr(trim($timeSlot), 0, 5);

                    // Check if already dispatched for this SUBSCRIPTION + DATE + SLOT
                    $alreadyDispatched = Order::where('SubscriptionID', $sub->SubscriptionID)
                        ->whereDate('ScheduledDate', $date)
                        ->where('DeliveryTime', $slot)
                        ->exists();

                    if ($alreadyDispatched) {
                        continue;
                    }

                    $payment = Payment::create(['Method' => 'Online', 'Status' => 'Completed', 'Amount' => 0]);
                    $custAddr = \App\Models\UserAddress::where('UserID', $sub->customer->UserID)->where('IsPrimary', true)->first();
                    $agent = DeliveryAgent::findBestForAddress(
                        $custAddr ? $custAddr->Address : '',
                        $custAddr ? $custAddr->Latitude : null,
                        $custAddr ? $custAddr->Longitude : null
                    );

                    $note = "📦 Meal Plan #{$sub->SubscriptionID} · Delivery for {$date->toDateString()} · Slot: {$slot}";

                    $order = Order::create([
                        'CustomerID'      => $sub->CustomerID,
                        'DeliveryAgentID' => $agent ? $agent->DeliveryAgentID : null,
                        'KitchenOwnerID'  => $sub->KitchenOwnerID, // Crucial for visibility!
                        'PaymentID'       => $payment->PaymentID,
                        'SubscriptionID'  => $sub->SubscriptionID,
                        'DeliveryTime'    => $slot,
                        'ScheduledDate'   => $date,
                        'TotalPrice'      => 0,
                        'OrderType'       => 'Meal Plan',
                        'OrderStatus'     => 'Pending',
                        'SpecialRequests' => $note,
                        'DeliveryCode'    => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    ]);

                    foreach ($approvedItems as $item) {
                        $order->menuItems()->attach($item->MenuItemID, ['Quantity' => 1]);
                    }

                    $totalDispatched++;
                }
            }
        }
        
        $this->info("Done! Dispatched {$totalDispatched} missing lifetime delivery order(s).");
    }
}
