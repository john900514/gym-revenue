<?php

declare(strict_types=1);

namespace App\Http\Controllers\Data;

use App\Actions\Endusers\Members\UpdateMemberCommunicationPreferences;
use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Projections\Location;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\EndUser;
use App\Domain\Users\Models\Member;
use App\Enums\LiveReportingEnum;
use App\Http\Controllers\Controller;
use App\Models\Clients\Features\Memberships\TrialMembershipType;
use App\Models\LiveReportsByDay;
use App\Models\ReadReceipt;
use App\Support\CurrentInfoRetriever;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Prologue\Alerts\Facades\Alert;

class MembersController extends Controller
{
    public function index(Request $request): InertiaResponse|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $client_id         = $user->client_id;
        $is_client_user    = $user->isClientUser();
        $page_count        = 10;
        $members           = [];
        $members_model     = $this->setUpMembersObject($client_id);
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();
        $current_team      = CurrentInfoRetriever::getCurrentTeam();
        $team_users        = $current_team->team_users()->get();
        $locations         = [];
        foreach ($locations_records as $location) {
            $locations[$location->gymrevenue_id] = $location->name;
        }

        if (! empty($members_model)) {
            $members = $members_model->with('location')
//                ->with('membershipType')
//                ->with('detailsDesc')
                ->with('notes')
                ->filter(
                    $request->only(
                        'search',
                        'trashed',
                        'createdat',
                        'grlocation',
                        'date_of_birth',
                        'nameSearch',
                        'phoneSearch',
                        'emailSearch',
                        'agreementSearch',
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
        if ($request->get('sort') !== '') {
            if ($request->get('dir') == 'DESC') {
                $sorted_result = $members->getCollection()->sortByDesc($request->get('sort'))->values();
            } else {
                $sorted_result = $members->getCollection()->sortBy($request->get('sort'))->values();
            }
            $members->setCollection($sorted_result);
        }

        if (CurrentInfoRetriever::getCurrentLocationID()) {
            $new_member_count = LiveReportsByDay::whereEntity('member')
                ->where('date', '=', date('Y-m-d'))
                ->whereAction(LiveReportingEnum::ADDED)
                ->whereGrLocationId(Location::find(CurrentInfoRetriever::getCurrentLocationID())->gymrevenue_id)
                ->first();

            if ($new_member_count !== null) {
                $new_member_count = $new_member_count->value;
            } else {
                $new_member_count = 0;
            }
        } else {
            $new_member_count = 0;
        }

        $available_member_owners = [];
        foreach ($team_users as $team_user) {
            $available_member_owners[$team_user->user_id] = "{$team_user->user->name}";
        }

        return Inertia::render('Members/Index', [
            'members'          => $members,
            'routeName'        => request()->route()->getName(),
            'title'            => 'Members',
            'owners'           => $available_member_owners,
            'locations'        => $locations,
            //'isClientUser' => $is_client_user,
            'filters'          => $request->all(
                'search',
                'trashed',
                'createdat',
                'grlocation',
                'claimed',
                'date_of_birth',
                'nameSearch',
                'phoneSearch',
                'emailSearch',
                'agreementSearch',
                'lastupdated'
            ),
            'grlocations'      => Location::all(),
            'new_member_count' => $new_member_count,
        ]);
    }

    public function claimed(Request $request): InertiaResponse
    {
        $prospects = [];

        return Inertia::render('Members/Index', [
            'leads'       => $prospects,
            'title'       => 'Leads',
            //'isClientUser' => $is_client_user,
            'filters'     => $request->all('search', 'trashed', 'createdat', 'grlocation'),
            'grlocations' => Location::all(),
        ]);
    }

    public function create(): InertiaResponse|RedirectResponse
    {
        //@TODO: we may want to embed the currentClientId in the form as a field
        //instead of getting the value here.  if you have multiple tabs open, and
        // one has an outdated currentClient id, creating would have unintended ]
        //consequences, potentially adding the member to the wrong client, or
        //just error out. also check for other areas in the app for similar behavior
        $user              = auth()->user();
        $client_id         = $user->client_id;
        $is_client_user    = $user->isClientUser();
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();

        if ($user->cannot('endusers.create', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }

        $locations = [];
        foreach ($locations_records as $location) {
            $locations[$location->gymrevenue_id] = $location->name;
        }

        return Inertia::render('Members/Create', [
            'user_id'   => $user->id,
            'locations' => $locations,
        ]);
    }

    public function edit(Member $end_user): InertiaResponse|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.update', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }
        //@TODO: we may want to embed the currentClientId in the form as a field
        //instead of getting the value here.  if you have multiple tabs open, and
        // one has an outdated currentClient id, creating would have unintended ]
        //consequences, potentially adding the member to the wrong client, or
        //just error out. also check for other areas in the app for similar behavior
        $client_id         = $user->client_id;
        $is_client_user    = $user->isClientUser();
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();

        $locations = [];
        foreach ($locations_records as $location) {
            $locations[$location->gymrevenue_id] = $location->name;
        }

        $end_user->load('notes');

        //for some reason inertiajs converts "notes" key to empty string.
        //so we set all_notes
        $member_data              = $end_user->toArray();
        $member_data['all_notes'] = UserAggregate::retrieve($end_user->id)->getNoteList('member');


        // if ($member_data['profile_picture_file_id']) {
        //     $member_data['profile_picture'] = File::whereId($member_data['profile_picture_file_id'])->first();
        // }

        foreach ($member_data as &$value) {
            if (ReadReceipt::whereNoteId($value['note_id'])->first()) {
                $value['read'] = true;
            } else {
                $value['read'] = false;
            }
        }

        return Inertia::render('Members/Edit', [
            'member'    => $member_data,
            'user_id'   => $user->id,
            'locations' => $locations,
        ]);
    }

    public function show(Member $end_user): InertiaResponse
    {
        $aggy = UserAggregate::retrieve($end_user->id);

        return Inertia::render('Members/Show', [
            'member'                => $end_user,
            'preview_note'          => $aggy->getNoteList('member'),
            'interactionCount'      => $aggy->getInteractionCount(),
            'trialMembershipTypes'  => TrialMembershipType::whereClientId(request()->user()->client_id)->get(),
            'hasTwilioConversation' => $end_user->client->hasTwilioConversationEnabled(),
        ]);
    }

    public function contact(Member $end_user): RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.contact', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back()->with('selectedMemberDetailIndex', 0);
        }

        if (array_key_exists('method', request()->all())) {
            $aggy = UserAggregate::retrieve($end_user->id);
            $data = request()->all();

            $data['interaction_count'] = 1; // start at one because this action won't be found in stored_events as it hasn't happened yet.
            foreach ($aggy->getAppliedEvents() as $value) {
                $contains = Str::contains(get_class($value), [
                    'MemberWasCalled',
                    'MemberWasTextMessaged',
                    'MemberWasEmailed',
                ]);
                if ($contains) {
                    $data['interaction_count']++;
                }
            }
            // Remove following If statement prior to going live
            if ($end_user->isCBorGR()) {
                switch (request()->get('method')) {
                    case 'email':
                        $aggy->email($data)->persist();
                        Alert::success("Email sent to member")->flash();

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
                Alert::error("Member does not have a Cape and Bay or Gym Revenue email address.")->flash();
            }
        }

        return Redirect::back()->with('selectedMemberDetailIndex', 0);
    }

    /**
     * @param Member $end_user
     *
     * @return array<string, mixed>|RedirectResponse
     */
    public function view(Member $end_user): array|RedirectResponse
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            Alert::error("Oops! You dont have permissions to do that.")->flash();

            return Redirect::back();
        }
        $data = Member::whereId($end_user->id)->first();
        $aggy = UserAggregate::retrieve($end_user->id);

        return [
            'member'           => $end_user,
            'user_id'          => $user->id,
            'club_location'    => Location::where('gymrevenue_id', $data->home_location_id)->first(),
            'interactionCount' => $aggy->getInteractionCount(),
            'preview_note'     => $aggy->getNoteList('member'),
        ];
    }

    //TODO:we could do a ton of cleanup here between shared codes with index. just ran out of time.

    /**
     * @param Request $request
     *
     * @return Collection<Member>
     */
    public function export(Request $request): Collection
    {
        $user = request()->user();
        if ($user->cannot('endusers.read', EndUser::class)) {
            abort(403);
        }

        $client_id     = $user->client_id;
        $members_model = $this->setUpMembersObject($client_id);

        if (! empty($members_model)) {
            $members = $members_model->with('location')->filter(
                $request->only(
                    'search',
                    'trashed',
                    'createdat',
                    'grlocation',
                    'date_of_birth',
                    'nameSearch',
                    'phoneSearch',
                    'emailSearch',
                    'agreementSearch',
                    'lastupdated'
                )
            )->orderBy('created_at', 'desc')->get();
        } else {
            $members = new Collection();
        }


        return $members;
    }

    public function memberCommunicationPreferences(Member $member): View
    {
        return view('comms-prefs', ['client' => $member->client, 'entity_type' => 'member', 'entity' => $member]);
    }

    public function updateMemberCommunicationPreferences(Request $request, Member $member): View
    {
        $member = UpdateMemberCommunicationPreferences::run($member->id, [
            'email' => ! ($request->subscribe_email === 'on'),
            'sms'   => ! ($request->subscribe_sms === 'on'),
        ]);

        return view('comms-prefs', [
            'client'      => $member->client,
            'entity'      => $member,
            'entity_type' => 'member',
            'success'     => true,
        ]);
    }

    private function setUpMembersObject(?string $client_id = null): null|Member|Builder
    {
        $results = null;
        if ($client_id !== null) {
            /**
             * BUSINESS RULES
             * 1. There must be an active client and an active team.
             * 2. Client Default Team, then all members from the client
             * 3. Else, get the team_locations for the active_team
             * 4. Query for client id and locations in
             */
            $current_team = CurrentInfoRetriever::getCurrentTeam();
            $client       = Client::find($client_id);

            if ($current_team->id !== $client->home_team_id) {
                $team_locations = $current_team->locations();

                if (count($team_locations) > 0) {
                    $results = Member::whereIn('home_location_id', $team_locations);
                }
            } else {
                $results = new Member();
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

        if ($client_id !== null) {
            $current_team = CurrentInfoRetriever::getCurrentTeam();
            $client       = Client::find($client_id);

            // The active_team is the current client's default_team (gets all the client's locations)
            if ($current_team->id === $client->home_team_id) {
                $results = new Location();
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
