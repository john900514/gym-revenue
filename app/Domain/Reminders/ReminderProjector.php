<?php

namespace App\Domain\Reminders;

use App\Domain\Reminders\Events\ReminderCreated;
use App\Domain\Reminders\Events\ReminderDeleted;
use App\Domain\Reminders\Events\ReminderTriggered;
use App\Domain\Reminders\Events\ReminderUpdated;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class ReminderProjector extends Projector
{
    public function onReminderCreated(ReminderCreated $event)
    {
        $reminder = (new Reminder())->writeable();
        $reminder->id = $event->payload['id'];
        $reminder->fill($event->payload);
        $reminder->save();
    }

    public function onReminderUpdated(ReminderUpdated $event)
    {
        Reminder::findOrFail($event->payload['id'])->writeable()->updateOrFail($event->payload);
    }

    public function onReminderDeleted(ReminderDeleted $event)
    {
        Reminder::findOrFail($event->payload['id'])->writeable()->delete();
    }

    public function onReminderTriggered(ReminderTriggered $event)
    {
        Reminder::findOrFail($event->payload['id'])->writeable()->updateOrFail(['triggered_at' => $event->createdAt()]);
    }
}
