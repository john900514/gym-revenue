<?php

declare(strict_types=1);

namespace App\Domain\CalendarEventTypes\Events;

use App\Domain\CalendarEventTypes\CalendarEventType;
use App\StorableEvents\EntityCreated;

class CalendarEventTypeCreated extends EntityCreated
{
    public function getEntity(): string
    {
        return CalendarEventType::class;
    }
}
