<?php

namespace App\Actions\Clients\Calendar;

use App\Aggregates\Clients\CalendarAggregate;
use App\Helpers\Uuid;
use App\Models\CalendarEvent;
use App\Models\CalendarEventType;
use App\Models\Endusers\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;
use Lorisleiva\Actions\Concerns\AsAction;


class CreateCalendarEvent
{
    use AsAction;

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' =>['required', 'string','max:50'],
            'description' => ['string', 'nullable'],
            'full_day_event' => ['required', 'boolean'],
            'start' => ['required'],
            'end' => ['required'],
            'event_type_id' => ['required', 'exists:calendar_event_types,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'attendees' => ['sometimes'],
            'lead_attendees' => ['sometimes'],
        ];
    }

    public function handle($data, $user = null)
    {
        $id = Uuid::new();
        $data['id'] = $id;
        $data['color'] = CalendarEventType::whereId($data['event_type_id'])->first()->color;; //Pulling eventType color for this table because that's how fullCalender.IO wants it

        if(isset($user->id))
            $data['attendees'][] = $user->id; //If you make the event, you're automatically an attendee.

        $attendees = [];
        if(!is_null($data['attendees'])) {
            $data['attendees'] = array_values(array_unique($data['attendees'])); //This will dupe check and then re-index the array.
            foreach($data['attendees'] as $user) {
                $user = User::whereId($user)->select('id', 'name', 'email')->first();
                if($user)
                    $attendees[] = $user;
            }
            $data['attendees'] = $attendees;
        }else {
            unset($data['attendees']);
        }


        $leadAttendees = [];
        if(!empty($data['lead_attendees'])) {
            $data['lead_attendees'] = array_values(array_unique($data['lead_attendees'])); //This will dupe check and then re-index the array.
            foreach($data['lead_attendees'] as $user) {
                $lead = Lead::whereId($user)->select('id', 'first_name', 'last_name', 'email')->first();
                if($user)
                    $leadAttendees[] = $lead;
            }
            $data['lead_attendees'] = $leadAttendees;
        } else {
            unset($data['lead_attendees']);
        }

        CalendarAggregate::retrieve($data['client_id'])
            ->createCalendarEvent($user->id ?? "Auto Generated", $data)
            ->persist();

        return CalendarEvent::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();
        return $current_user->can('calendar.create', CalendarEvent::class);
    }

    public function asController(ActionRequest $request)
    {

        $calendar = $this->handle(
            $request->validated(),
            $request->user()
        );

        Alert::success("Calendar Event '{$calendar->title}' was created")->flash();

        return Redirect::back();
    }

}
