<?php

namespace App\Modules\Purchase\Mail;

use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchaseQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $comments, $quotation, $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $comments, PurchaseQuotation $quotation)
    {
        $this->subject = $subject;
        $this->quotation = $quotation;
        $this->comments = $comments; // $message é variável reservada
        $this->user = auth()->user();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.purchase-quotation');
    }
}
