<?php

namespace App\Domain\CalendarEvents\Events;

use App\Domain\CalendarEvents\CalendarEvent;
use App\StorableEvents\EntityUpdated;

class CalendarEventUpdated extends EntityUpdated
{
    public function getEntity(): string
    {
        return CalendarEvent::class;
    }
}
