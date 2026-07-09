<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\UserAddress;

class SimulateDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-driver {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulates a driver moving from the kitchen to the delivery address for a specific order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = Order::with(['kitchenOwner', 'caterer', 'customer.user', 'subscription.kitchen'])->find($orderId);

        if (!$order) {
            $this->error("Order ID {$orderId} not found.");
            return;
        }

        if ($order->OrderStatus !== 'Delivering') {
            if ($this->confirm("Order status is '{$order->OrderStatus}'. Change it to 'Delivering'?", true)) {
                $order->OrderStatus = 'Delivering';
                $order->save();
            } else {
                $this->info('Simulation cancelled.');
                return;
            }
        }

        $kitchenLat = $order->kitchenOwner ? $order->kitchenOwner->Latitude : ($order->caterer ? $order->caterer->Latitude : null);
        $kitchenLng = $order->kitchenOwner ? $order->kitchenOwner->Longitude : ($order->caterer ? $order->caterer->Longitude : null);
        
        // If it's a meal plan / subscription order, get it from the subscription relation
        if (!$kitchenLat && $order->subscription && $order->subscription->kitchen) {
            $kitchenLat = $order->subscription->kitchen->Latitude;
            $kitchenLng = $order->subscription->kitchen->Longitude;
        }

        $primaryAddress = UserAddress::where('UserID', $order->customer->UserID)->where('IsPrimary', true)->first();
        if (!$primaryAddress) {
            $this->error("Customer does not have a primary address.");
            return;
        }

        $deliveryLat = $primaryAddress->Latitude;
        $deliveryLng = $primaryAddress->Longitude;

        if (!$kitchenLat || !$kitchenLng || !$deliveryLat || !$deliveryLng) {
            $this->error("Missing coordinates. Kitchen: [{$kitchenLat}, {$kitchenLng}], Delivery: [{$deliveryLat}, {$deliveryLng}]");
            return;
        }

        $this->info("Starting simulation from Kitchen to Delivery Address...");
        $this->info("Kitchen: {$kitchenLat}, {$kitchenLng}");
        $this->info("Delivery: {$deliveryLat}, {$deliveryLng}");

        // We will do 20 steps. Wait 3 seconds between each step to match the 3s polling.
        $steps = 30;
        $waitSeconds = 3;

        for ($i = 0; $i <= $steps; $i++) {
            $progress = $i / $steps;
            
            // Linear interpolation
            $currentLat = $kitchenLat + ($deliveryLat - $kitchenLat) * $progress;
            $currentLng = $kitchenLng + ($deliveryLng - $kitchenLng) * $progress;

            $order->DriverLatitude = $currentLat;
            $order->DriverLongitude = $currentLng;
            $order->save();

            $this->info(sprintf("Step %02d/%02d | Driver is at: %.6f, %.6f", $i, $steps, $currentLat, $currentLng));
            
            if ($i < $steps) {
                sleep($waitSeconds);
            }
        }

        $this->info("Driver has reached the destination!");
        if ($this->confirm("Change order status to 'Delivered'?", true)) {
            $order->OrderStatus = 'Delivered';
            $order->save();
            $this->info("Order delivered!");
        }
    }
}
