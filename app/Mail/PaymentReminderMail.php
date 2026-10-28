<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class PaymentReminderMail extends Mailable
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
        return $this->subject('Payment Reminder: 2 Days Left - Booking '.$this->booking->ref_code)
            ->view('emails.payment-reminder');
    }
}
