<?php

namespace App\StorableEvents\Clients\Reminder;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ReminderCreated extends ShouldBeStored
{
    public $user, $payload;

    public function __construct(string $user, array $payload)
    {
        $this->user = $user;
        $this->payload = $payload;
    }
}
