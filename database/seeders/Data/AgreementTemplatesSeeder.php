<?php

namespace Database\Seeders\Data;

use App\Domain\AgreementTemplates\Actions\CreateAgreementTemplate;
use App\Domain\Clients\Projections\Client;
use Illuminate\Database\Seeder;
use Symfony\Component\VarDumper\VarDumper;

class AgreementTemplatesSeeder extends Seeder
{
    public function run()
    {
        $amountOfAgreements = 10;
        if (env('QUICK_SEED')) {
            $amountOfAgreements = 1;
        }
        VarDumper::dump('Getting Clients');
        // Get all the Clients
        $clients = Client::whereActive(1)
            ->get();
        if (count($clients) > 0) {
            foreach ($clients as $client) {
                VarDumper::dump($client->name);
                // For each client, get all the locations
                if (count($client->locations) > 0) {
                    foreach ($client->locations as $idx => $location) {
                        VarDumper::dump('Generating Agreement Templates for ' . $client->name . '!');
                        for ($x = 0; $x <= $amountOfAgreements; $x++) {
                            $agreement_data['client_id'] = $client->id;
                            $agreement_data['gr_location_id'] = $location->gymrevenue_id;
                            $agreement_data['agreement_name'] = 'test contract for '.$client->name;
                            $agreement_data['agreement_json'] = json_encode($client);
                            CreateAgreementTemplate::run($agreement_data);
                        }
                    }
                }
            }
        }
    }
}