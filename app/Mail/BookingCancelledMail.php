<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\Booking;

class BookingCancelledMail extends Mailable
{
    use Queueable;

    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Booking Cancellation Notice';
        if ($this->booking->cancelled_by === 'customer') {
            $subject = 'Booking Cancellation Confirmation';
        } elseif ($this->booking->cancelled_by === 'system') {
            $subject = 'Booking Cancelled - Payment Deadline Expired';
        }

        return $this->subject($subject)
                    ->view('emails.booking-cancelled');
    }
}
