<?php

namespace App\Projectors\Clients;

use App\Models\Calendar\CalendarAttendee;
use App\StorableEvents\Clients\Calendar\CalendarAttendeeAdded;
use App\StorableEvents\Clients\Calendar\CalendarAttendeeDeleted;
use App\StorableEvents\Clients\Calendar\CalendarAttendeeInvited;
use App\StorableEvents\Clients\Calendar\CalendarInviteAccepted;
use App\StorableEvents\Clients\Calendar\CalendarInviteDeclined;
use Mailgun\Mailgun;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class CalendarAttendeeProjector extends Projector
{

    public function onCalendarAttendeeAdded(CalendarAttendeeAdded $event)
    {
        CalendarAttendee::create($event->data);
    }

    public function onCalendarAttendeeDeleted(CalendarAttendeeDeleted $event)
    {
        CalendarAttendee::whereEntityType($event->data['entity_type'])->whereEntityId($event->data['entity_id'])->forceDelete();
    }

    public function onCalendarAttendeeInvited(CalendarAttendeeInvited $event)
    {
        $mg = Mailgun::create(env('MAILGUN_SECRET'));
        $mg->messages()->send(env('MAILGUN_DOMAIN'), [
            'from'    => env('MAIL_FROM_ADDRESS'),
            'to'      => 'blair@capeandbay.com',//$event->data['entity_data']['email'],
            'subject' => $event->data['subject'],
            'html'    => $event->data['body'],
        ]);
    }


    public function onCalendarInviteAccepted(CalendarInviteAccepted $event)
    {
        CalendarAttendee::whereId($event->data['attendeeData']['id'])->update(array('invitation_status' => 'Accepted'));
    }

    public function onCalendarInviteDeclined(CalendarInviteDeclined $event)
    {
        CalendarAttendee::whereId($event->data['attendeeData']['id'])->update(array('invitation_status' => 'Declined'));
    }


}

