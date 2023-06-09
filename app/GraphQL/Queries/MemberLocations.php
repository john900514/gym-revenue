<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Projections\Location;
use App\Domain\Teams\Models\Team;
use Illuminate\Support\Collection;

final class MemberLocations
{
    private function setUpLocationsObject(bool $is_client_user, ?string $client_id = null): Location
    {
        $results = new Location();
        if ($client_id !== null) {
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

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $_args
     *
     * @return Collection<Location>
     */
    public function __invoke($_, array $_args): Collection
    {
        // TODO implement the resolver
        $client_id      = request()->user()->client_id;
        $is_client_user = request()->user()->isClientUser();

        return $this->setUpLocationsObject($is_client_user, $client_id)?->get();
    }
}
