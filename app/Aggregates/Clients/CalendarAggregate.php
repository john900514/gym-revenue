<?php

namespace App\Aggregates\Clients;


use App\StorableEvents\Clients\Calendar\CalendarEventCreated;
use App\StorableEvents\Clients\Calendar\CalendarEventTypeCreated;
use App\StorableEvents\Clients\Calendar\CalendarEventDeleted;
use App\StorableEvents\Clients\Calendar\CalendarEventUpdated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class CalendarAggregate extends AggregateRoot
{

    public function createCalendarEvent(string $title, $start, $end, $type)
    {
        $this->recordThat(new CalendarEventCreated($this->uuid(), $title, $start, $end, $type));
        return $this;
    }

    public function updateCalendarEvent(string $id, $title, $start, $end)
    {
        $this->recordThat(new CalendarEventUpdated($this->uuid(), $id, $title, $start, $end));
        return $this;
    }

    public function deleteCalendarEvent(string $id, $title, $start, $end)
    {
        $this->recordThat(new CalendarEventDeleted($this->uuid(), $id, $title, $start, $end));
        return $this;
    }

    public function createCalendarEventType(string $name, $desc, $type)
    {
        $this->recordThat(new CalendarEventTypeCreated($this->uuid(), $name, $desc, $type));
        return $this;
    }

}
