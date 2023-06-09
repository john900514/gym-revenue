<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Domain\CalendarEvents\CalendarEvent;
use App\Domain\Reminders\Reminder;
use App\Domain\Users\Models\Lead;
use App\Domain\Users\Models\Member;
use App\Domain\Users\Models\User;

//TODO: remove this in favor of a standard db query
//TODO: 1.) stop separating the attendees into different arrays, instead do that clientside by filtering on attendeee type
//TODO: 2.) remove this class in favor of just using a CalendarEvent @find query
final class CalendarEventById
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): CalendarEvent
    {
        // TODO implement the resolver
        $event            = CalendarEvent::with('type', 'attendees', 'files')
            ->find($args["id"]);
        $user_attendees   = [];
        $lead_attendees   = [];
        $member_attendees = [];
        if ($event->attendees) {
            foreach ($event->attendees as $attendee) {
                if ($attendee->entity_type == User::class) {
                    if (request()->user()->id == $attendee->entity_id) {
                        $event['my_reminder'] = Reminder::whereEntityType(CalendarEvent::class)
                            ->whereEntityId($event['id'])
                            ->whereUserId($attendee->entity_id)
                            ->first();

                        $event['im_attending'] = true;
                    }
                    $user_attendees[] = [
                        'id' => $attendee->entity_id,
                        'reminder' => Reminder::whereEntityType(CalendarEvent::class)
                            ->whereEntityId($event['id'])
                            ->whereUserId($attendee->entity_id)
                            ->first() ?? null,
                    ];
                }

                $call_outcome = null;
                if ($attendee->entity_type == Lead::class) {
                    $lead_attendees[]['id'] = $attendee->entity_id;
                    //TODO: Create Projection Table for User Communication History
//                    $call_outcome = EndUserDetails::whereField('call_outcome')
//                        ->whereEndUserId($attendee->entity_id)
//                        ->whereEntityId($event->id)->first();
                }
                if ($attendee->entity_type == Member::class) {
                    $member_attendees[]['id'] = $attendee->entity_id;
                    //TODO: Create Projection Table for User Communication History
//                    $call_outcome = EndUserDetails::whereField('call_outcome')
//                        ->whereEndUserId($attendee->entity_id)
//                        ->whereEntityId($event->id)->first();
                }
                if ($event->call_task == 1) {
                    if (isset($call_outcome)) {
                        $event->callOutcome   = $call_outcome['value'];
                        $event->callOutcomeId = $call_outcome['id'];
                    }
                }
            }
        }
        $event->user_attendees   = $user_attendees;
        $event->lead_attendees   = $lead_attendees;
        $event->member_attendees = $member_attendees;

        return $event;
    }
}
