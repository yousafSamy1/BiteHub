<?php

namespace App\Services;

use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspensionNotification;
use Carbon\Carbon;

class ProfanityFilterService
{
    /**
     * List of banned words/patterns (English and Arabic).
     */
    protected $bannedWords = [
        // English (common)
        'fuck', 'shit', 'bitch', 'asshole', 'dick', 'pussy', 'slut', 'whore', 'bastard', 'cunt',
        
        // Arabic (common/slang - simplified)
        'شرموط', 'كس', 'طيز', 'زب', 'لبوة', 'منيوك', 'خول', 'عرص', 'قحبة', 'سكس', 'نيك', 'متناك'
    ];

    /**
     * Check if text contains profanity.
     */
    public function hasProfanity($text)
    {
        $text = mb_strtolower($text);
        
        foreach ($this->bannedWords as $word) {
            // Use word boundaries for English to avoid false positives (e.g. "Assistance")
            if (preg_match('/[a-z]/i', $word)) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            } else {
                // For Arabic, word boundaries \b don't work well with Unicode
                $pattern = '/' . preg_quote($word, '/') . '/u';
            }

            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Process a violation: increment strikes, report, and auto-suspend if needed.
     */
    public function checkAndProcess(User $user, $messageText)
    {
        if (!$this->hasProfanity($messageText)) {
            return true; // Clean
        }

        // 1. Handle Strike Reset (30 days logic)
        $now = Carbon::now();
        if ($user->LastViolationAt && Carbon::parse($user->LastViolationAt)->diffInDays($now) > 30) {
            $user->ProfanityStrikes = 0;
        }

        // 2. Increment Strikes
        $user->ProfanityStrikes += 1;
        $user->LastViolationAt = $now;
        
        $isSuspended = false;
        if ($user->ProfanityStrikes >= 3) {
            $user->Status = 'Suspended';
            $isSuspended = true;
        }
        
        $user->save();

        // 3. Create Support Ticket (Report to Admin)
        SupportTicket::create([
            'UserID'      => $user->UserID,
            'SenderType'  => $user->Role,
            'Category'    => 'Profanity Violation',
            'Subject'     => 'Automated Profanity Report - Strike #' . $user->ProfanityStrikes,
            'Description' => "User [{$user->FullName}] (ID: {$user->UserID}) attempted to send a message containing profanity.\n\n" .
                             "Offending Message:\n\"{$messageText}\"\n\n" .
                             "Current Strikes: {$user->ProfanityStrikes}/3\n" .
                             ($isSuspended ? "STATUS: Account has been automatically SUSPENDED." : "STATUS: Warning issued."),
            'Status'      => 'Open',
        ]);

        // 4. Send Email if Suspended
        if ($isSuspended) {
            try {
                Mail::to($user->Email)->send(new SuspensionNotification($user));
            } catch (\Exception $e) {
                // Log or fail silently
                \Log::error("Failed to send suspension email to {$user->Email}: " . $e->getMessage());
            }
        }

        return false; // Blocked
    }
}
