<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class PaymentConfirmedMail extends Mailable
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
        return $this->subject('Payment Confirmed - Booking '.$this->booking->ref_code)
            ->view('emails.payment-confirmed');
    }
}
