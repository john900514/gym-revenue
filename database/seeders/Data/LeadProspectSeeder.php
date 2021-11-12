<?php

namespace Database\Seeders\Data;

use App\Aggregates\Endusers\EndUserActivityAggregate;
use App\Models\Clients\Client;
use App\Models\Endusers\Lead;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Symfony\Component\VarDumper\VarDumper;

class LeadProspectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VarDumper::dump('Getting Clients');
        // Get all the Clients
        $clients = Client::whereActive(1)
            ->with('locations')->get();

        if(count($clients) > 0)
        {
            foreach ($clients as $client)
            {
                VarDumper::dump($client->name);
                // For each client, get all the locations
                if(count($client->locations) > 0)
                {
                    foreach($client->locations as $idx => $location)
                    {
                        VarDumper::dump($location->name);
                        // For each location, MAKE 25 users, don't create
                        $prospects = Lead::factory()->count(25)
                            // over ride the client id and gr id from the factory
                            ->client_id($client->id)
                            ->gr_location_id($location->gymrevenue_id)
                            ->state(new Sequence(
                                // alternate the lead types
                                ['lead_type' => 'free_trial'],
                                ['lead_type' => 'grand_opening'],
                                ['lead_type' => 'streaming_preview'],
                                ['lead_type' => 'personal_training'],
                                ['lead_type' => 'app_referral'],
                                ['lead_type' => 'facebook'],
                                ['lead_type' => 'snapchat'],
                                ['lead_type' => 'contact_us'],
                                ['lead_type' => 'mailing_list'],
                            ))->make();

                        VarDumper::dump('Generating Leads!');
                        foreach ($prospects as $prospect) {
                            // For each fake user, run them through the EnduserActivityAggregate
                            EndUserActivityAggregate::retrieve($prospect->id)
                                ->createNewLead($prospect->toArray())
                                ->persist();
                        }

                    }
                }
            }
        }
        /*
         * STEPS
         * 1.
         * 2.
         * 3.
         *      -
         *      -
         * 4.
         */
    }
}
