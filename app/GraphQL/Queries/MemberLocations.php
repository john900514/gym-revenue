<?php

namespace App\GraphQL\Queries;

use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Projections\Location;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamDetail;

final class MemberLocations
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
        $client_id = request()->user()->client_id;
        $is_client_user = request()->user()->isClientUser();
        $locations_records = $this->setUpLocationsObject($is_client_user, $client_id)->get();

        return $locations_records;
    }

    private function setUpLocationsObject(bool $is_client_user, string $client_id = null)
    {
        $results = [];
        if ((! is_null($client_id))) {
            $session_team = session()->get('current_team');
            if ($session_team && array_key_exists('id', $session_team)) {
                $current_team = Team::find($session_team['id']);
            } else {
                $current_team = Team::find(auth()->user()->default_team_id);
            }
            $client = Client::find($client_id);

            // The active_team is the current client's default_team (gets all the client's locations)
            if ($current_team->id == $client->home_team_id) {
                $results = new Location();
            } else {
                // The active_team is not the current client's default_team
                $team_locations = TeamDetail::whereTeamId($current_team->id)
                    ->where('field', '=', 'team-location')
                    ->get();

                if (count($team_locations) > 0) {
                    $in_query = [];
                    // so get the teams listed in team_details
                    foreach ($team_locations as $team_location) {
                        $in_query[] = $team_location->value;
                    }

                    $results = Location::whereIn('gymrevenue_id', $in_query);
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