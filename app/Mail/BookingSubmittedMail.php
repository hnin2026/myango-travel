<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BookingSubmittedMail extends Mailable
{
    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Booking Request Received')
            ->view('emails.booking-submitted');
    }
}
