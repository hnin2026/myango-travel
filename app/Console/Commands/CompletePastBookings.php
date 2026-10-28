<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class CompletePastBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:complete-past';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark paid bookings whose tour checkout date has passed as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        // Total paid bookings checked
        $checked = Booking::where('status', Booking::STATUS_PAID)->count();

        $completed = 0;

        // Process in chunks of 100 for memory and query efficiency
        Booking::where('status', Booking::STATUS_PAID)
            ->where('checkout_date', '<', $today)
            ->chunkById(100, function ($bookings) use (&$completed) {
                foreach ($bookings as $booking) {
                    $booking->update([
                        'status' => Booking::STATUS_COMPLETED,
                    ]);
                    $completed++;
                }
            });

        $skipped = $checked - $completed;

        $this->info("Paid bookings checked: {$checked}");
        $this->info("Bookings marked completed: {$completed}");
        $this->info("Bookings skipped: {$skipped}");

        return 0;
    }
}
