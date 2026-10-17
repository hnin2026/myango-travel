<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\PaymentReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically send payment reminder emails for confirmed bookings 2 days before payment deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Target date is exactly 2 calendar days before the payment deadline
        $targetDate = now()->addDays(2)->format('Y-m-d');

        // Total eligible bookings query:
        // Must be status = confirmed, no payment receipt uploaded, payment deadline exists,
        // payment reminder not yet sent, and deadline is exactly 2 calendar days from today.
        $query = Booking::where('status', 'confirmed')
            ->whereNull('payment_receipt')
            ->whereNotNull('payment_deadline')
            ->whereNull('payment_reminder_sent_at')
            ->whereDate('payment_deadline', $targetDate);

        $checked = (clone $query)->count();
        $sent = 0;
        $failed = 0;

        // Process in chunks of 100
        $query->chunkById(100, function ($bookings) use (&$sent, &$failed) {
            foreach ($bookings as $booking) {
                try {
                    $booking->load(['tour']);
                    Mail::to($booking->email)->send(new PaymentReminderMail($booking));

                    // Only update payment_reminder_sent_at if email dispatch succeeded
                    $booking->update([
                        'payment_reminder_sent_at' => now(),
                    ]);

                    $sent++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("Failed to send payment reminder for booking {$booking->ref_code}: " . $e->getMessage());
                    Log::error("Failed to send payment reminder for booking {$booking->ref_code}: " . $e->getMessage());
                }
            }
        });

        $this->info("Eligible bookings checked: {$checked}");
        $this->info("Payment reminders sent: {$sent}");
        if ($failed > 0) {
            $this->warn("Payment reminders failed: {$failed}");
        }

        return 0;
    }
}
