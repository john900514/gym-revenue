<?php

namespace App\Projectors\Clients;

use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\StorableEvents\Clients\Calendar\CalendarEventCreated;
use App\StorableEvents\Clients\Calendar\CalendarEventTypeCreated;
use App\StorableEvents\Clients\Calendar\CalendarEventDeleted;
use App\StorableEvents\Clients\Calendar\CalendarEventUpdated;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class CalendarProjector extends Projector
{
    public function onCalenderEventCreated(CalendarEventCreated $event)
    {
        CalendarEvent::create([
            'client_id' => $event->client,
            'title' => $event->title,
            'full_day_event' => false,
            'start' => $event->start,
            'end' => $event->end,
            'options' => $event->options ?? null,
            'event_type_id' => $event->type,
        ]);
    }


    public function onCalenderEventUpdated(CalendarEventUpdated $event)
    {
        CalendarEvent::createOrUpdateRecord([
            'id' => $event->id,
            'client_id' => $event->client,
            'title' => $event->title,
            'full_day_event' => false,
            'start' => $event->start,
            'end' => $event->end,
            'options' => $event->options ?? null,
            'event_type_id' => $event->type,
        ]);
    }


    public function onCalenderEventDeleted(CalendarEventDeleted $event)
    {
        CalendarEvent::withTrashed()->findOrFail($event->id)->forceDelete();
    }

    public function onCalenderEventTypeCreated(CalendarEventTypeCreated $event)
    {
        $event = CalendarEventType::create([
            'client_id' => $event->client,
            'name' => $event->name,
            'description' => $event->desc,
            'type' => $event->type,
        ]);
    }
}

