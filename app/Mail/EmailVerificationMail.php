<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use SerializesModels;

    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param  string  $verificationUrl
     * @return void
     */
    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Please verify your email address')
                    ->view('emails.verify')
                    ->with(['verificationUrl' => $this->verificationUrl]);
    }
}
