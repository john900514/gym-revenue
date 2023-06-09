<?php

declare(strict_types=1);

namespace App\Http\Controllers\Data;

use App\Domain\Clients\Projections\Client;
use App\Domain\EntrySources\EntrySource;
use App\Domain\LeadTypes\LeadType;
use App\Domain\Locations\Projections\Location;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\Employee;
use App\Domain\Users\Models\EndUser;
use App\Domain\Users\Models\Lead;
use App\Enums\LiveReportingEnum;
use App\Http\Controllers\Controller;
use App\Models\Clients\Features\Memberships\TrialMembershipType;
use App\Models\LiveReportsByDay;
use App\Models\ReadReceipt;
use App\Support\CurrentInfoRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Prologue\Alerts\Facades\Alert;

class LeadsController extends Controller
{
    public function index(Request $request): InertiaResponse|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $client_id       = $user->client_id;
        $page_count      = 10;
        $prospects       = [];
        $prospects_model = $this->setUpLeadsObject($client_id);
        $opportunities   = Lead::select('opportunity')->distinct()->get()->pluck('opportunity');

        if (! empty($prospects_model)) {
            $prospects = $prospects_model->with('location')
                ->with('membershipType')
                //->with('entrySource')
                ->with('notes')
                ->filter(
                    $request->only(
                        'search',
                        'trashed',
                        'typeoflead',
                        'createdat',
                        'grlocation', //'entrysource',
                        'claimed',
                        'opportunity',
                        'claimed',
                        'date_of_birth',
                        'nameSearch',
                        'phoneSearch',
                        'emailSearch',
                        'lastupdated'
                    )
                )
                ->orderBy('created_at', 'desc')
                ->sort()
                ->paginate($page_count)
                ->appends(request()->except('page'));
        }

        //THIS DOESN'T WORK BECAUSE OF PAGINATION BUT IT MAKES IT LOOK LIKE IT'S WORKING FOR NOW
        //MUST FIX BY DEMO 6/15/22
        //THIS BLOCK HAS TO BE REMOVED & QUERIES REWRITTEN WITH JOINS SO ACTUAL SORTING WORKS WITH PAGINATION
        if ($request->get('sort') != '') {
            if ($request->get('dir') == 'DESC') {
                $sorted_result = $prospects->getCollection()->sortByDesc($request->get('sort'))->values();
            } else {
                $sorted_result = $prospects->getCollection()->sortBy($request->get('sort'))->values();
            }
            $prospects->setCollection($sorted_result);
        }

        $current_team          = CurrentInfoRetriever::getCurrentTeam();
        $team_users            = Employee::whereIn(
            'id',
            $current_team->team_users()
                ->get()
                ->pluck('user_id')
                ->toArray()
        )->get();
        $available_lead_owners = [];
        foreach ($team_users as $team_user) {
            $available_lead_owners[] = [
                'name' => $team_user->name,
                "id"   => $team_user->id,
            ];
        }

        if (CurrentInfoRetriever::getCurrentLocationID()) {
            $new_lead = LiveReportsByDay::whereEntity('lead')
                ->where('date', '=', date('Y-m-d'))
                ->whereAction(LiveReportingEnum::ADDED)
                ->whereGrLocationId(Location::find(CurrentInfoRetriever::getCurrentLocationID())->gymrevenue_id)
                ->first();

            if ($new_lead !== null) {
                $new_lead_count = $new_lead->value;
            } else {
                $new_lead_count = 0;
            }
        } else {
            $new_lead_count = 0;
        }

        return Inertia::render('Leads/Index', [
            'leads'         => $prospects,
            'routeName'     => request()->route()->getName(),
            'title'         => 'Leads',
            //'isClientUser' => $is_client_user,
            'filters'       => $request->all(
                'search',
                'trashed',
                'typeoflead',
                'createdat',
                'grlocation',
                'entrysource',
                'claimed',
                'opportunity',
                'claimed',
                'date_of_birth',
                'nameSearch',
                'phoneSearch',
                'emailSearch',
                'lastupdated'
            ),
            'owners'        => $available_lead_owners,
            'grlocations'   => Location::all(),
            'entrysources'  => EntrySource::all(),
            'opportunities' => array_values($opportunities->toArray()),
            'newLeadCount'  => $new_lead_count,
            'lead_types'    => LeadType::all(),
        ]);
    }

    public function claimed(Request $request): InertiaResponse
    {
        $client_id = $request->user()->currentClientId();

        $page_count = 10;
        $prospects  = [];

        $prospects_model = $this->setUpLeadsObjectclaimed($client_id);

        $locations    = Location::all();
        $entry_source = EntrySource::all();

        if (! empty($prospects_model)) {
            $prospects = $prospects_model->with('location')
                ->with('leadType')
                ->with('membershipType')
                ->with('entrySource')
                ->with('detailsDesc')
                ->filter($request->only('search', 'trashed', 'typeoflead', 'createdat', 'grlocation', 'entrysource'))
                ->orderBy('created_at', 'desc')
                ->paginate($page_count)
                ->appends($request->except('page'));
        }

        return Inertia::render('Leads/Index', [
            'leads'        => $prospects,
            'title'        => 'Leads',
            'filters'      => $request->all(
                'search',
                'trashed',
                'typeoflead',
                'createdat',
                'grlocation',
                'entrysource'
            ),
            'lead_types'   => LeadType::all(),
            'grlocations'  => $locations,
            'entrysources' => $entry_source,
        ]);
    }

    public function create(): InertiaResponse|RedirectResponse
    {
        //@TODO: we may want to embed the currentClientId in the form as a field
        //instead of getting the value here.  if you have multiple tabs open, and
        // one has an outdated currentClient id, creating would have unintended ]
        //consequences, potentially adding the lead to the wrong client, or
        //just error out. also check for other areas in the app for similar behavior
        $user              = auth()->user();
        $client_id         = ($request_user = request()->user())->client_id;
        $is_client_user    = $request_user->isClientUser();
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();
        $current_team      = CurrentInfoRetriever::getCurrentTeam();
        $team_users        = $current_team->team_users()->get();


        if ($user->cannot('endusers.create', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $locations = [];
        foreach ($locations_records as $location) {
            $locations[$location->gymrevenue_id] = $location->name;
        }

        //$lead_types = LeadType::all();
        $entry_sources = EntrySource::all();
        //$lead_statuses = LeadStatus::all();


        /**
         * STEPS for team users
         * 1. No CnB Admins unless it is you
         * 2. Unless Cnb Admin, no admin users
         */
        $available_lead_owners = [];
        foreach ($team_users as $team_user) {
            $available_lead_owners[$team_user->user_id] = "{$team_user->user->name}";
        }

        return Inertia::render('Leads/Create', [
            'user_id'       => $user->id,
            'locations'     => $locations,
            //'lead_types' => $lead_types,
            'entry_sources' => $entry_sources,
            //'lead_statuses' => $lead_statuses,
            'lead_owners'   => $available_lead_owners,
        ]);
    }

    public function edit(Lead $end_user): InertiaResponse|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.update', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $client_id         = $user->client_id;
        $is_client_user    = $user->isClientUser();
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();

        $locations = [];
        foreach ($locations_records as $location) {
            $locations[$location->gymrevenue_id] = $location->name;
        }

        $current_team = CurrentInfoRetriever::getCurrentTeam();
        $team_users   = $current_team->team_users()->get();
        /**
         * STEPS for team users
         * 1. No CnB Admins unless it is you
         * 2. Unless Cnb Admin, no admin users
         */
        $available_lead_owners = [];
        foreach ($team_users as $team_user) {
            $available_lead_owners[$team_user->user_id] = "{$team_user->user->name}";
        }

        //for some reason inertiajs converts "notes" key to empty string.
        //so we set all_notes
        $lead_data              = $end_user->toArray();
        $lead_data['all_notes'] = UserAggregate::retrieve($end_user->id)->getNoteList('lead');


        // if ($lead_data['profile_picture_file_id']) {
        //     $lead_data['profile_picture'] = File::whereId($lead_data['profile_picture_file_id'])->first();
        // }

        foreach ($lead_data['all_notes'] as &$value) {
            if (ReadReceipt::whereNoteId($value['note_id'])->first()) {
                $value['read'] = true;
            } else {
                $value['read'] = false;
            }
        }

        return Inertia::render('Leads/Edit', [
            'lead'        => $lead_data,
            'user_id'     => $user->id,
            'locations'   => $locations,
            'lead_owners' => $available_lead_owners,
        ]);
    }

    public function show(Lead $end_user): InertiaResponse
    {
        $aggy = UserAggregate::retrieve($end_user->id);


        return Inertia::render('Leads/Show', [
            'lead'                  => $end_user,
            'preview_note'          => $aggy->getNoteList('lead'),
            'interactionCount'      => $aggy->getInteractionCount(),
            'trialMembershipTypes'  => TrialMembershipType::whereClientId(request()->user()->client_id)->get(),
            'hasTwilioConversation' => $end_user->client->hasTwilioConversationEnabled(),
        ]);
    }

    public function contact(Lead $end_user): \Illuminate\Http\RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.contact', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back()->with('selectedLeadDetailIndex', 0);
        }


        if (array_key_exists('method', request()->all())) {
            $aggy = UserAggregate::retrieve($end_user->id);
            $data = request()->all();

            $data['interaction_count'] = 1; // start at one because this action won't be found in stored_events as it hasn't happened yet.
            foreach ($aggy->getAppliedEvents() as $value) {
                $contains = Str::contains(get_class($value), [
                    'LeadWasCalled',
                    'LeadWasTextMessaged',
                    'LeadWasEmailed',
                ]);
                if ($contains) {
                    $data['interaction_count']++;
                }
            }

            $data['user'] = auth()->user()->id;
            // Remove following If statement prior to going live
            if ($end_user->isCBorGR()) {
                switch (request()->get('method')) {
                    case 'email':
                        $aggy->email($data)->persist();
                        Alert::success("Email sent to lead")->flash();

                        break;

                    case 'phone':
                        $aggy->logPhoneCall($data)->persist();
                        Alert::success("Call Log Updated")->flash();

                        break;

                    case 'sms':
                        $aggy->textMessage($data)->persist();
                        Alert::success("SMS Sent")->flash();

                        break;

                    default:
                        Alert::error("Invalid communication method. Select Another.")->flash();
                }
            } else {
                Alert::error("Lead does not have a Cape and Bay or Gym Revenue email address.")->flash();
            }
        }

        return Redirect::back()->with('selectedLeadDetailIndex', 0);
    }

    public function sources(): InertiaResponse
    {
        return Inertia::render('Leads/Sources', [
            'sources' => EntrySource::get(['id', 'name']),
        ]);
    }

    public function statuses(): InertiaResponse
    {
        return Inertia::render('Leads/Statuses');
    }

    /**
     * @param Lead $end_user
     *
     * @return array<string, mixed>|RedirectResponse
     */
    public function view(Lead $end_user): array|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $lead_aggy = UserAggregate::retrieve($end_user->id);
        $locid     = Location::where('gymrevenue_id', $end_user->home_location_id)->first();

        return [
            'lead'             => $end_user,
            'user_id'          => $user->id,
            'club_location'    => $locid,
            'interactionCount' => $lead_aggy->getInteractionCount(),
            'preview_note'     => $lead_aggy->getNoteList('lead'),
        ];
    }

    //TODO:we could do a ton of cleanup here between shared codes with index. just ran out of time.

    /**
     * @param Request $request
     *
     * @return Collection<Lead>
     */
    public function export(Request $request): Collection
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            abort(403);
        }

        $client_id       = request()->user()->client_id;
        $prospects_model = $this->setUpLeadsObject($client_id);

        if (! empty($prospects_model)) {
            $prospects = $prospects_model->with('location')
                ->with('leadType')
                ->with('membershipType')
                ->with('entrydSource')
                ->with('claimed')
                ->with('detailsDesc')
                ->filter(
                    $request->only(
                        'search',
                        'trashed',
                        'typeoflead',
                        'createdat',
                        'grlocation',
                        'entrysource',
                        'opportunity',
                        'claimed',
                        'date_of_birth',
                        'nameSearch',
                        'phoneSearch',
                        'emailSearch',
                        'lastupdated'
                    )
                )
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $prospects = new Collection();
        }

        return $prospects;
    }

    private function setUpLeadsObject(?string $client_id = null): null|Lead|Builder
    {
        $results = null;
        if ($client_id !== null) {
            /**
             * BUSINESS RULES
             * 1. There must be an active client and an active team.
             * 2. Client Default Team, then all leads from the client
             * 3. Else, get the team_locations for the active_team
             * 4. Query for client id and locations in
             */
            $current_team = CurrentInfoRetriever::getCurrentTeam();
            $client       = Client::find($client_id);

            if ($current_team->id != $client->home_team_id) {
                $team_locations = $current_team->locations();
                // @todo - we will probably need to do some user-level scoping
                // example - if there is scoping and this club is not there, don't include it

                $results = Lead::whereIn('home_location_id', $team_locations);
            } else {
                $results = new Lead();
            }
        }

        return $results;
    }

    private function setUpLeadsObjectclaimed(?string $client_id = null): null|Builder
    {
        $results = null;

        if ($client_id !== null) {
            /**
             * BUSINESS RULES
             * 1. There must be an active client and an active team.
             * 2. Client Default Team, then all leads from the client
             * 3. Else, get the team_locations for the active_team
             * 4. Query for client id and locations in
             */
            $current_team = CurrentInfoRetriever::getCurrentTeam();
            $client       = Client::find($client_id);

            if ($current_team->name != $client->home_team_id) {
                $team_locations = $current_team->locations();

                if (count($team_locations) > 0) {
                    $results = Lead::whereIn('home_location_id', $team_locations)->whereHas('claimed');
                }
            } else {
                $results = Lead::whereHas('claimed');
            }
        }

        return $results;
    }

    private function setUpLocationsObject(bool $is_client_user, ?string $client_id = null): null|Location|Builder
    {
        $results = null;
        /**
         * BUSINESS RULES
         * 1. All Locations
         *  - Cape & Bay user
         *  - The active_team is the current client's default_team (gets all the client's locations)
         * 2. Scoped Locations
         *  - The active_team is not the current client's default_team
         *      so get the teams listed in team_details
         * 3. No Locations
         *  - The active_team is not the current client's default_team
         *      but there are no locations assigned in team_details
         *  - (Bug or Feature?) - The current client is null (cape & bay)
         *      but the user is not a cape & bay user.
         */

        /*$locations = ($client_id !== null)
            ? Location::whereClientId($client_id)
            : new Location();
        */

        if ($client_id !== null) {
            $current_team = CurrentInfoRetriever::getCurrentTeam();
            $client       = Client::find($client_id);

            // The active_team is the current client's default_team (gets all the client's locations)
            if ($current_team->id == $client->home_team_id) {
                $results = Location::whereClientId($client_id);
            } else {
                // The active_team is not the current client's default_team
                $team_locations = $current_team->locations();

                if (count($team_locations) > 0) {
                    $results = Location::whereIn('gymrevenue_id', $team_locations);
                }
            }
        } else {
            // Cape & Bay user
            if (! $is_client_user) {
                $results = new Location();
            }
        }

        return $results;
    }
}
