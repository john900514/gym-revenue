<?php

namespace Database\Seeders\Clients;

use App\Domain\Locations\Projections\Location;
use App\Domain\Teams\Actions\UpdateTeam;
use App\Domain\Teams\Models\Team;
use Illuminate\Database\Seeder;

class TeamLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('RAPID_SEED') === true) {
            $client_teams = [
                // The Kalamazoo
                [
                    'team_id' => Team::fetchTeamIDFromName('Zoo Sales Team'),
                    'name' => 'team-location',
                    'value' => 'TKH001',
                ],
                [
                    'team_id' => Team::fetchTeamIDFromName('The Kalamazoo 1 HQ'),
                    'name' => 'team-location',
                    'value' => 'TKH001',
                ],
            ];
        } else {
            $client_teams = [
                // The Z
                [
                    'team_id' => Team::fetchTeamIDFromName('Big Island Team'),
                    'name' => 'team-location',
                    'value' => 'TZ04',
                ],
                [
                    'team_id' => Team::fetchTeamIDFromName('Oahu Team'),
                    'name' => 'team-location',
                    'value' => 'TZ01',
                ],
                [
                    'team_id' => Team::fetchTeamIDFromName('Oahu Team'),
                    'name' => 'team-location',
                    'value' => 'TZ01',
                ],
                [
                    'team_id' => Team::fetchTeamIDFromName('Big Island Boating & CrossFit Club'),
                    'name' => 'team-location',
                    'value' => 'TZ01',
                ],
                // Stencils
                [
                    'team_id' => Team::fetchTeamIDFromName('Stencils Portland'),
                    'name' => 'team-location',
                    'value' => Location::whereLocationNo('ST01')->first()->gymrevenue_id,
                ],
                // SciFi Purple Gyms
                [
                    'team_id' => Team::fetchTeamIDFromName('SciFi NC'),
                    'name' => 'team-location',
                    'value' => Location::whereLocationNo('PF01')->first()->gymrevenue_id,
                ],
                [
                    'team_id' => Team::fetchTeamIDFromName('Purple FL'),
                    'name' => 'team-location',
                    'value' => Location::whereLocationNo('PF01')->first()->gymrevenue_id,
                ],

            ];
        }
        foreach ($client_teams as $client_team) {
            echo("{$client_team['value']}: {$client_team['team_id']}\n");
            UpdateTeam::run(
                Team::withoutGlobalScopes()->findOrFail(
                    $client_team['team_id']
                ),
                ['locations' => [$client_team['value']]]
            );
        }
    }
}
