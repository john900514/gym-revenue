<?php

namespace Database\Seeders\Data;

use App\Domain\Clients\Projections\Client;
use App\Models\Clients\Features\Memberships\TrialMembershipType;
use Illuminate\Database\Seeder;
use Symfony\Component\VarDumper\VarDumper;

class TrialMembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trial_membership_types = [
            ['type_name' => 'default_free_trial', 'slug' => 'default-free-trial', 'trial_length' => 14],
            ['type_name' => 'default_guest_pass', 'slug' => 'default-guest-pass', 'trial_length' => 1],
        ];

        $clients = Client::all();
        foreach ($clients as $client) {
            $client_locations = $client->locations()->pluck('id');
            foreach ($trial_membership_types as $trial_membership_type) {
                TrialMembershipType::create(array_merge($trial_membership_type, [
                    'client_id' => $client->id,
                    'active' => 1,
                    'locations' => $client_locations,
                ]));
                VarDumper::dump("Adding Trial Membership Type {$trial_membership_type['type_name']}");
            }
        }
    }
}
