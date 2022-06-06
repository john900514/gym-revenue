<?php

namespace App\Actions\Endusers\Leads;

use App\Models\Clients\Client;
use App\Models\Endusers\Lead;
use App\Models\TeamDetail;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ReadLeads
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
            'per_page' => 'sometimes|required',
        ];
    }

    public function handle($data, $current_user = null)
    {
        if (array_key_exists('per_page', $data)) {
            $page_count = $data['per_page'] > 0 && $data['per_page'] < 1000 ? $data['per_page'] : 10;
        } else {
            $page_count = 10;
        }


        $prospects = [];
        //$prospects_model = $this->setUpLeadsObject(request()->user()->isClientUser(), request()->user()->currentClientId());
        $prospects_model = $this->setUpLeadsObject(true, '2f108597-fe62-458f-ac30-a159936f7aae');


        if (! empty($prospects_model)) {
            $prospects = $prospects_model
                ->with('location')
                ->with('leadType')
                ->with('membershipType')
                ->with('leadSource')
                ->with('leadsclaimed')
                ->with('detailsDesc')
                ->with('opportunity')
                ->with('notes')
                ->orderBy('created_at', 'desc')
                ->sort()
                ->paginate($page_count)
                ->appends(request()->except('page'));
        }

        return $prospects;
    }

    public function asController(ActionRequest $request)
    {
        $leads = $this->handle(
            $request->validated(),
            $request->user(),
        );

        if ($request->wantsJson()) {
            return $leads;
        }
    }

    private function setUpLeadsObject(bool $is_client_user, string $client_id = null)
    {
        $results = [];

        if ((! is_null($client_id))) {
            $current_team = request()->user()->currentTeam()->first();
            $client = Client::find($client_id);

            $team_locations = [];

            if ($current_team->id != $client->home_team_id) {
                $team_locations_records = TeamDetail::whereTeamId($current_team->id)
                    ->where('name', '=', 'team-location')->get();

                if (count($team_locations_records) > 0) {
                    foreach ($team_locations_records as $team_locations_record) {
                        // @todo - we will probably need to do some user-level scoping
                        // example - if there is scoping and this club is not there, don't include it
                        $team_locations[] = $team_locations_record->value;
                    }

                    $results = Lead::whereClientId($client_id)
                        ->whereIn('gr_location_id', $team_locations);
                }
            } else {
                $results = Lead::whereClientId($client_id);
            }
        }


        return $results;
    }
}
