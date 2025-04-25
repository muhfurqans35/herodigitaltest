<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantInvitationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invitationLink;
    public $tenantName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $invitationLink, string $tenantName)
    {
        $this->invitationLink = $invitationLink;
        $this->tenantName = $tenantName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Undangan bergabung ke tenant {$this->tenantName}")
            ->view('emails.tenant_invitation')
            ->with([
                'invitationLink' => $this->invitationLink,
                'tenantName' => $this->tenantName,
            ]);
    }
}
