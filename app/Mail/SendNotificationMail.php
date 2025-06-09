<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
            // return $this->view('emails.notification')
            //     ->with([
            //             'messageContent' => $this->details['messageContent'],
            //         ]);
       
            return $this->html( $this->details['messageContent'])
            ->subject('Notification Email');
    
    }
}
