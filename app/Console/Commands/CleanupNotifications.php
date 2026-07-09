<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Carbon\Carbon;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup notifications older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Notification::where('CreatedAt', '<', Carbon::now()->subDays(30))->delete();
        $this->info("Successfully deleted {$count} old notifications.");
    }
}
