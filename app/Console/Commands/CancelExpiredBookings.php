<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\BookingCancelledMail;
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
        $now = now();

        // 1. Expired bookings are all bookings with status = confirmed, a payment deadline, and deadline < current date/time
        // Bookings that are skipped include confirmed bookings with deadlines in the future.
        // Get total number of confirmed bookings with deadlines to calculate checked & skipped correctly.
        $checked = Booking::where('status', 'confirmed')
            ->whereNotNull('payment_deadline')
            ->count();

        $cancelled = 0;

        // Using chunkById for efficient query processing in case of a large bookings table
        Booking::where('status', 'confirmed')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', $now)
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
                        $this->error("Failed to send cancellation email for booking {$booking->ref_code}: " . $e->getMessage());
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
