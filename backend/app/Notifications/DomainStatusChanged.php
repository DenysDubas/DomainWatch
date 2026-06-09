<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\CheckStatus;
use App\Models\Domain;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainStatusChanged extends Notification
{
    public function __construct(
        public readonly Domain $domain,
        public readonly CheckStatus $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = strtoupper($this->status->value);

        return (new MailMessage)
            ->subject("[{$label}] Domain \"{$this->domain->name}\" status changed")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your domain **{$this->domain->url}** is now **{$this->status->value}**.")
            ->when($this->status === CheckStatus::Down, fn ($mail) => $mail->line('Please check your server or hosting provider.'))
            ->action('Open Dashboard', rtrim(config('app.frontend_url'), '/') . '/domains/' . $this->domain->id)
            ->line('You receive this because your domain status changed.');
    }
}
