<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailySummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySummaryMail;
use Carbon\Carbon;

class SendDailySummaryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send the daily summary PDF report to admins';

    /**
     * Execute the console command.
     */
    public function handle(DailySummaryService $summaryService)
    {
        $this->info('Gathering data...');
        $data = $summaryService->getSummaryData();
        $dateString = Carbon::now()->format('Y-m-d');

        $this->info('Generating PDF...');
        $pdf = Pdf::loadView('reports.daily_summary_pdf', compact('data'));
        $pdfContent = $pdf->output();

        $this->info('Sending emails...');
        
        $emails = env('ADMIN_REPORT_EMAILS', 'admin@example.com');
        $emailArray = array_map('trim', explode(',', $emails));

        foreach ($emailArray as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new DailySummaryMail($pdfContent, $dateString));
                $this->info("Sent to: {$email}");
            }
        }

        $this->info('Daily summary report completed successfully!');
        return 0;
    }
}
