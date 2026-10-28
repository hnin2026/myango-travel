<?php

namespace App\Console\Commands;

use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel bookings whose payment deadline has expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        // 1. Expired bookings are all bookings with status = confirmed, a payment deadline, and deadline date strictly before today.
        // Bookings whose deadline is today (or in the future) are not expired yet.
        // Get total number of confirmed bookings with deadlines to calculate checked & skipped correctly.
        $checked = Booking::where('status', 'confirmed')
            ->whereNull('payment_receipt')
            ->whereNotNull('payment_deadline')
            ->count();

        $cancelled = 0;

        // Using chunkById for efficient query processing in case of a large bookings table
        Booking::where('status', 'confirmed')
            ->whereNull('payment_receipt')
            ->whereNotNull('payment_deadline')
            ->whereDate('payment_deadline', '<', $today)
            ->chunkById(100, function ($bookings) use (&$cancelled) {
                foreach ($bookings as $booking) {
                    // Update cancellation details
                    $booking->update([
                        'status' => 'cancelled',
                        'cancelled_by' => 'system',
                        'cancel_reason' => 'Payment deadline expired',
                        'cancelled_at' => now(),
                    ]);

                    $cancelled++;

                    // Send cancellation email to customer
                    try {
                        $booking->load(['tour']);
                        Mail::to($booking->email)->send(new BookingCancelledMail($booking));
                    } catch (\Exception $e) {
                        $this->error("Failed to send cancellation email for booking {$booking->ref_code}: ".$e->getMessage());
                    }
                }
            });

        $skipped = $checked - $cancelled;

        // Output summary in the console
        $this->info("Expired bookings checked: {$checked}");
        $this->info("Bookings cancelled: {$cancelled}");
        $this->info("Bookings skipped: {$skipped}");
    }
}
