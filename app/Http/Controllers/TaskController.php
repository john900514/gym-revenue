<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\CalendarEvents\CalendarEvent;
use App\Domain\CalendarEventTypes\CalendarEventType;
use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Projections\Location;
use App\Domain\Reminders\Reminder;
use App\Domain\Teams\Models\TeamUser;
use App\Domain\Users\Models\Employee;
use App\Domain\Users\Models\Lead;
use App\Domain\Users\Models\Member;
use App\Support\CurrentInfoRetriever;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TaskController extends Controller
{
    public function index(Request $request): InertiaResponse|RedirectResponse
    {
        $client_id = $request->user()->client_id;
        if ($client_id === null) {
            return Redirect::route('dashboard');
        }

        /** CHECKING THE PAGE REQUEST FOR START PARAMS
         * If param 'start' doesn't exist, we get the start and end of today.
         * If param 'start' exists, we get the start and end of that date
         */
        if (! $request->has('start')) {
            $request->merge([
                'start' => date(
                    'Y-m-d H:i:s',
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        (new DateTime())->format('Y-m-d 00:00:00')
                    )
                                        ->getTimestamp()
                ),
            ]);
            $request->merge([
                'end' => date(
                    'Y-m-d H:i:s',
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        (new DateTime())->format('Y-m-d 23:59:59')
                    )
                                        ->getTimestamp()
                ),
            ]);
        } else {
            $date = date('Y-m-d', strtotime($request->get('start')));
            $request->merge([
                'start' => date(
                    'Y-m-d H:i:s',
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        (new DateTime())->format($date . ' 00:00:00')
                    )
                                        ->getTimestamp()
                ),
            ]);
            $request->merge([
                'end' => date(
                    'Y-m-d H:i:s',
                    DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        (new DateTime())->format($date . ' 23:59:59')
                    )
                                        ->getTimestamp()
                ),
            ]);
        }

        $client_task_type = CalendarEventType::whereClientId($client_id)->whereType('Task')->first();

        //page won't load if no events type: task exist. The below fixes that.
        if ($client_task_type !== null) {
            $incomplete_tasks = CalendarEvent::with('owner')
                ->whereEventTypeId($client_task_type->id)
                ->whereOwnerId(request()->user()->id)
                ->whereNull('event_completion')
                ->with('type')
                ->filter($request->only('search', 'start', 'end'))
                ->paginate(10);

            $completed_tasks = CalendarEvent::with('owner')
                ->whereEventTypeId($client_task_type->id)
                ->whereOwnerId(request()->user()->id)
                ->whereNotNull('event_completion')
                ->with('type')
                ->filter($request->only('search', 'start', 'end'))
                ->paginate(10);

            $overdue_tasks = CalendarEvent::with('owner')
                ->whereEventTypeId($client_task_type->id)
                ->whereOwnerId(request()->user()->id)
                ->whereNull('event_completion')
                ->whereDate('start', '<', date('Y-m-d H:i:s'))
                ->with('type')
                ->filter($request->only('search', 'start', 'end'))
                ->paginate(10);

            $incomplete_tasks = $this->modifyEventArray($incomplete_tasks);
            $completed_tasks  = $this->modifyEventArray($completed_tasks);
            $overdue_tasks    = $this->modifyEventArray($overdue_tasks);
        } else {
            $incomplete_tasks = [];
            $completed_tasks  = [];
            $overdue_tasks    = [];
        }

        $current_team = CurrentInfoRetriever::getCurrentTeam();
        $client       = Client::with(['home_team'])->find($client_id);

        $is_home_team = $client->home_team_id == $current_team->id;

        // If the active team is a client's-default team get all members
        if ($is_home_team) {
            $users = Employee::whereClientId($client_id)->get();
        } else {
            // else - get the members of that team
            $team_users = TeamUser::whereTeamId($current_team->id)->get();
            $user_ids   = [];
            foreach ($team_users as $team_user) {
                $user_ids[] = $team_user->user_id;
            }
            $users = Employee::whereIn('id', $user_ids)->get();
        }

        return Inertia::render('Task/Show', [
            'client_id'            => $client_id,
            'client_users'         => $users,
            'lead_users'           => Lead::select('id', 'first_name', 'last_name')->get(),
            'member_users'         => Member::select('id', 'first_name', 'last_name')->get(),
            'locations'            => Location::select('id', 'name')->get(),
            'calendar_event_types' => CalendarEventType::whereClientId($client_id)->whereName('Task')->get(),
            'filters'              => $request->all('search', 'trashed', 'state'),
            'incomplete_tasks'     => $incomplete_tasks,
            'overdue_tasks'        => $overdue_tasks,
            'completed_tasks'      => $completed_tasks,
        ]);
    }

    /**
     * @param LengthAwarePaginator $array
     *
     */
    public function modifyEventArray(LengthAwarePaginator $array): LengthAwarePaginator
    {
        $user_id = request()->user()->id;
        foreach ($array as $key => $event) {
            $user_attendees   = [];
            $lead_attendees   = [];
            $member_attendees = [];
            if ($event->attendees) {
                foreach ($event->attendees as $attendee) {
                    if ($attendee->entity_type == Employee::class) {
                        if ($user_id == $attendee->entity_id) {
                            $array[$key]['my_reminder'] = Reminder::whereEntityType(CalendarEvent::class)
                                ->whereEntityId($event['id'])
                                ->whereUserId($attendee->entity_id)
                                ->first();

                            $array[$key]['im_attending'] = true;
                        }
                        $user_attendees[] = [
                            'id'       => (int) $attendee->entity_id,
                            'reminder' => Reminder::whereEntityType(CalendarEvent::class)
                                    ->whereEntityId($event['id'])
                                    ->whereUserId($attendee->entity_id)
                                    ->first() ?? null,
                        ];
                    }

                    $call_outcome = null;
                    if ($attendee->entity_type == Lead::class) {
                        $lead_attendees[]['id'] = $attendee->entity_id;

                        try {
                            //TODO: Create Projection Table for User Communication History
//
//                            $call_outcome = UserDetails::whereField('call_outcome')
//                                ->whereUserId($attendee->entity_id)
//                                ->where('misc->entity_id', $event->id)
//                                ->orderBy('created_at', 'desc')
//                                ->first();
                        } catch (\Exception $e) {
                        }
                    }
                    if ($attendee->entity_type == Member::class) {
                        $member_attendees[]['id'] = $attendee->entity_id;

                        try {
                            //TODO: Create Projection Table for User Communication History
//                            $call_outcome = UserDetails::whereField('call_outcome')
//                                ->whereUserId($attendee->entity_id)
//                                ->where('misc->entity_id', $event->id)
//                                ->orderBy('created_at', 'desc')
//                                ->first()
//                            ;
                        } catch (\Exception $e) {
                        }
                    }
                    if ($event->call_task == 1) {
                        if (isset($call_outcome)) {
                            $event->callOutcome   = $call_outcome['value'];
                            $event->callOutcomeId = $call_outcome['id'];
                        }
                    }
                }
            }
            $array[$key]->user_attendees   = $user_attendees;
            $array[$key]->lead_attendees   = $lead_attendees;
            $array[$key]->member_attendees = $member_attendees;
        }

        return $array;
    }
}
