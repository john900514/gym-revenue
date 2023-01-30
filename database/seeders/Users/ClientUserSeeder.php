<?php

namespace Database\Seeders\Users;

use App\Domain\Clients\Projections\Client;
use App\Domain\Departments\Department;
use App\Domain\Teams\Models\Team;
use App\Domain\Users\Models\User;
use App\Enums\UserTypesEnum;
use Illuminate\Database\Seeder;
use Silber\Bouncer\Database\Role;
use Symfony\Component\VarDumper\VarDumper;

class ClientUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $amountOfSalesReps = 5;
        $amountOfElse = 2;
        if (env('QUICK_SEED')) {
            $amountOfSalesReps = 1;
            $amountOfAssociates = 1;
            $amountOfTrainers = 1;
            $amountOfElse = 1;
        }
        VarDumper::dump("Adding Factory Users...");

        $clients = Client::all();
        foreach ($clients as $client) {
            $client = Client::with('teams')->find($client->id);
            $departments = Department::where('client_id', $client->id)->get();
            VarDumper::dump("Adding ".$client->name." Users...");

            /** Find all teams for client and put the names in an array */
            $roles = Role::whereScope($client->id)->get();

            /** Collect all teams into an array that so everyone is on every team */
            $team_ids = $client->teams()->pluck('id');
            $teams = Team::whereIn('id', $team_ids)->get();
            $team_names = [];
            foreach ($teams as $team) {
                $team_names[] = $team['name'];
            }


            foreach ($roles as $role) {
                if ($role['title'] !== 'Employee') {
                    if ($role['title'] === 'Sales Rep') {
                        $users = User::factory()
                            ->count($amountOfSalesReps)
                            ->make([
                                'client' => $client->name,
                                'role_id' => $role['id'],
                                'team_names' => $team_names,
                            ]);
                    } else {
                        $users = User::factory()
                            ->count(1)
                            ->make([
                                'client' => $client->name,
                                'role_id' => $role['id'],
                                'team_names' => $team_names,
                            ]);
                    }

                    foreach ($users as $user) {
                        $department = $departments[rand(0, count($departments) - 1)];
                        $positions = $department->positions()->get()->pluck('id')->toArray();
                        $position_id = null;

                        while (sizeof($positions) === 0) {
                            $department = $departments[rand(0, count($departments) - 1)];
                            $positions = $department->positions()->get()->pluck('id')->toArray();
                        }

                        $position_id = $positions[array_rand($positions)];

                        $client = Client::whereName($user['client'])->first();
                        $teams = Team::whereIn('name', $user['team_names'])->get();
                        $team_ids = $teams->pluck('id');
                        // $possible_home_locations = $teams->pluck('locations')->flatten()->keyBy('value')->values()->pluck('value');
                        $possible_home_locations = [];

                        foreach ($teams as $team) {
                            $possible_home_locations = array_merge($possible_home_locations, $team->locations());
                        }

                        if (sizeof($possible_home_locations) > 0) {
                            $home_location_id = $possible_home_locations[array_rand($possible_home_locations)];
                        } else {
                            $home_location_id = null;
                        }
                        $senior_managers = [3, 2, 1];
                        $managers = [4];
                        $manager = in_array($user['role_id'], $senior_managers) ? 'Senior Manager' : (in_array($user['role_id'], $managers) ? 'Manager' : null);
                        $final_data = array_merge($user->toArray(), [
                            'client_id' => $client->id,
                            'password' => 'Hello123!',
                            'team_ids' => $team_ids,
                            'home_location_id' => $home_location_id,
                            'manager' => $manager,
                            'user_type' => UserTypesEnum::EMPLOYEE,
                            'departments' => [
                                ['department' => $department->id, 'position' => $position_id],
                            ],
                        ]);
                        \App\Domain\Users\Actions\CreateUser::run($final_data);
                    }
                }
            }
        }
    }
}
