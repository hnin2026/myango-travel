<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminInquirySubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function build()
    {
        $subject = 'New Inquiry Received - ' . ($this->inquiry->reference ?? 'INQ-XXXX');
        return $this->subject($subject)
                    ->view('emails.admin-inquiry-submitted');
    }
}
