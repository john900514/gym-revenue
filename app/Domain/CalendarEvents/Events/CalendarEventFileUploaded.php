<?php

declare(strict_types=1);

namespace App\Domain\CalendarEvents\Events;

use App\Domain\CalendarEvents\CalendarEvent;
use App\StorableEvents\EntityCreated;

class CalendarEventFileUploaded extends EntityCreated
{
    public function getEntity(): string
    {
        return CalendarEvent::class;
    }
}
