<?php

declare(strict_types=1);

namespace App\Domain\CalendarEventTypes;

use App\Domain\CalendarEventTypes\Events\CalendarEventTypeCreated;
use App\Domain\CalendarEventTypes\Events\CalendarEventTypeDeleted;
use App\Domain\CalendarEventTypes\Events\CalendarEventTypeRestored;
use App\Domain\CalendarEventTypes\Events\CalendarEventTypeTrashed;
use App\Domain\CalendarEventTypes\Events\CalendarEventTypeUpdated;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class CalendarEventTypeProjector extends Projector
{
    public function onCalendarEventTypeCreated(CalendarEventTypeCreated $event): void
    {
        $calendarEventType = (new CalendarEventType())->writeable();
        $calendarEventType->forceFill(['client_id' => $event->clientId(), 'id' => $event->aggregateRootUuid()]);
        $calendarEventType->fill($event->payload);
        $calendarEventType->save();
    }

    public function onCalendarEventTypeUpdated(CalendarEventTypeUpdated $event): void
    {
        CalendarEventType::findOrFail($event->aggregateRootUuid())->writeable()->updateOrFail($event->payload);
    }

    public function onCalendarEventTypeTrashed(CalendarEventTypeTrashed $event): void
    {
        CalendarEventType::findOrFail($event->aggregateRootUuid())->writeable()->deleteOrFail();
    }

    public function onCalendarEventTypeRestored(CalendarEventTypeRestored $event): void
    {
        CalendarEventType::withTrashed()->findOrFail($event->aggregateRootUuid())->writeable()->restore();
    }

    public function onCalendarEventTypeDeleted(CalendarEventTypeDeleted $event): void
    {
        CalendarEventType::withTrashed()->findOrFail($event->aggregateRootUuid())->writeable()->forceDelete();
    }
}
