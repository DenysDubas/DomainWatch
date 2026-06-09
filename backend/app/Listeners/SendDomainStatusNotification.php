<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DomainStatusChanged;
use App\Notifications\DomainStatusChanged as DomainStatusChangedNotification;

class SendDomainStatusNotification
{
    public function handle(DomainStatusChanged $event): void
    {
        $user = $event->domain->owner;

        if ($user) {
            $user->notify(new DomainStatusChangedNotification($event->domain, $event->status));
        }
    }
}
