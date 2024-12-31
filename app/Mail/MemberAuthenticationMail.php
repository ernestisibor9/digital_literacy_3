<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberAuthenticationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.auth_member') // Ensure this is the correct view path
                    ->with([
                        'firstname' => $this->user->firstname,
                        'verificationUrl' => url('auth/verify/' . $this->user->email . '/' . $this->password),
                    ])
                    ->subject('Please authenticate your account');
    }
}
