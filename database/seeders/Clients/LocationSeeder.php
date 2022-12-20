<?php

namespace Database\Seeders\Clients;

use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Actions\CreateLocation;
use App\Domain\Locations\Actions\ImportLocations;
use App\Domain\Locations\Projections\Location;
use App\Enums\LocationTypeEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\VarDumper\VarDumper;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locations = [
            // The Kalamazoo
            [
                'client' => 'The Kalamazoo',
                'name' => 'The Kalamazoo 1 HQ',
                'state' => 'LA',
                'city' => 'Shreveport',
                'zip' => '71101',
                'location_no' => '001',
                'location_type' => LocationTypeEnum::HQ,
                //'gymrevenue_id' => 'TK12'
            ],
            // FitnessTruth
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 1',
                'state' => 'TX',
                'city' => 'Abbott',
                'zip' => '76621',
                'location_no' => 'FT13',
                'location_type' => LocationTypeEnum::OFFICE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 2',
                'state' => 'TX',
                'city' => 'Abernathy',
                'zip' => '79311',
                'location_no' => 'FT14',
                'location_type' => LocationTypeEnum::OFFICE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 3',
                'state' => 'TX',
                'city' => 'Abram',
                'zip' => '78572',
                'location_no' => 'FT15',
                'location_type' => LocationTypeEnum::OFFICE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 4',
                'state' => 'TX',
                'city' => 'Abbott',
                'zip' => '76621',
                'location_no' => 'FT16',
                'location_type' => LocationTypeEnum::OFFICE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 5',
                'state' => 'TX',
                'city' => 'Abernathy',
                'zip' => '79311',
                'location_no' => 'FT17',
                'location_type' => LocationTypeEnum::STORE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 6',
                'state' => 'TX',
                'city' => 'Abbott',
                'zip' => '76621',
                'location_no' => 'FT18',
                'location_type' => LocationTypeEnum::STORE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 7',
                'state' => 'TX',
                'city' => 'Abbott',
                'zip' => '76621',
                'location_no' => 'FT19',
                'location_type' => LocationTypeEnum::STORE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 8',
                'state' => 'TX',
                'city' => 'Abernathy',
                'zip' => '79311',
                'location_no' => 'TR40',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'FT20'
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 9',
                'state' => 'TX',
                'city' => 'Abbott',
                'zip' => '76621',
                'location_no' => 'FT21',
                'location_type' => LocationTypeEnum::STORE,
            ],
            [
                'client' => 'FitnessTruth',
                'name' => 'FitnessTruth 10',
                'state' => 'TN',
                'city' => 'Adams',
                'zip' => '37010',
                'location_no' => 'FT22',
                'location_type' => LocationTypeEnum::STORE,
            ],
            // The Z
            [
                'client' => 'The Z',
                'name' => 'Zoo 1',
                'state' => 'HI',
                'city' => 'Honolulu',
                'zip' => '96795',
                'location_no' => 'TZ01',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'TZ01'
            ],
            [
                'client' => 'The Z',
                'name' => 'Zoo 2',
                'state' => 'HI',
                'city' => 'Honolulu',
                'zip' => '96795',
                'location_no' => 'TZ02',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'TZ02'
            ],
            [
                'client' => 'The Z',
                'name' => 'Zoo 3',
                'state' => 'HI',
                'city' => 'Honolulu',
                'zip' => '96796',
                'location_no' => 'TZ03',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'TZ03'
            ],
            [
                'client' => 'The Z',
                'name' => 'Zoo 4',
                'state' => 'HI',
                'city' => 'Honolulu',
                'zip' => '96797',
                'location_no' => 'TZ04',
                'location_type' => LocationTypeEnum::STORE,
                // 'gymrevenue_id' => 'TZ04'
            ],
            [
                'client' => 'The Z',
                'name' => 'Zoo 5',
                'state' => 'HI',
                'city' => 'Honolulu',
                'zip' => '96798',
                'location_no' => 'TZ05',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'TZ05'
            ],
            [
                'client' => 'The Z',
                'name' => 'Zoo 6',
                'state' => 'HI',
                'city' => 'Waimanalo',
                'zip' => '96795',
                'location_no' => 'TZ06',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'TZ06'
            ],

            // Stencils
            [
                'client' => 'Stencils',
                'name' => 'Stencils 1',
                'state' => 'CA',
                'city' => 'Los Angeles',
                'zip' => '90001',
                'location_no' => 'ST01',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST07'
            ],
            [
                'client' => 'Stencils',
                'name' => 'Stencils 2',
                'state' => 'CA',
                'city' => 'San Diego',
                'zip' => '22400',
                'location_no' => 'ST02',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST08'
            ],
            [
                'client' => 'Stencils',
                'name' => 'Stencils 3',
                'state' => 'CA',
                'city' => 'San Jose',
                'zip' => '94088',
                'location_no' => 'ST03',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST09'
            ],
            [
                'client' => 'Stencils',
                'name' => 'Stencils 4',
                'state' => 'OR',
                'city' => 'Portland',
                'zip' => '97035',
                'location_no' => 'ST05',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST10'
            ],
            [
                'client' => 'Stencils',
                'name' => 'Stencils @ Microsoft',
                'state' => 'Wa',
                'city' => 'Redmond',
                'zip' => '98052',
                'location_no' => 'STMS',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST11'
            ],
            [
                'client' => 'Stencils',
                'name' => 'Stencils 5',
                'state' => 'OR',
                'city' => 'Portland',
                'zip' => '97035',
                'location_no' => 'ST05',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST11'
            ],
            // SciFi Purple Gyms
            [
                'client' => 'Sci-Fi Purple Gyms',
                'state' => 'NC',
                'city' => 'Advance',
                'zip' => '27006',
                'location_no' => 'PF01',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'PF26'
            ],
            [
                'client' => 'Sci-Fi Purple Gyms',
                'state' => 'NC',
                'city' => 'Advance',
                'zip' => '27006',
                'location_no' => 'PF02',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'PF27'
            ],
            [
                'client' => 'Sci-Fi Purple Gyms',
                'state' => 'FL',
                'city' => 'Ponte Vedra Beach',
                'zip' => '32004',
                'location_no' => 'PF03',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'PF28'
            ],
            [
                'client' => 'Sci-Fi Purple Gyms',
                'state' => 'FL',
                'city' => 'Callahan',
                'zip' => '32011',
                'location_no' => 'PF04',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'PF29'
            ],
            // iFit
            [
                'client' => 'iFit',
                'state' => 'FL',
                'city' => 'Tampa',
                'zip' => '33605',
                'location_no' => 'I01',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF30'
            ],
            [
                'client' => 'iFit',
                'state' => 'FL',
                'city' => 'Lake City',
                'zip' => '32025',
                'location_no' => 'I02',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF31'
            ],
            [
                'client' => 'iFit',
                'state' => 'FL',
                'city' => 'Hilliard',
                'zip' => '32046',
                'location_no' => 'I03',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF32'
            ],
            [
                'client' => 'iFit',
                'state' => 'FL',
                'city' => 'Orange Park',
                'zip' => '32065',
                'location_no' => 'I04',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF33'
            ],
            [
                'client' => 'iFit',
                'state' => 'FL',
                'city' => 'Tampa',
                'zip' => '33601',
                'location_no' => 'I05',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF34'
            ],
            [
                'client' => 'iFit',
                'state' => 'GA',
                'city' => 'Atlanta',
                'zip' => '30301',
                'location_no' => 'I06',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF35'
            ],
            [
                'client' => 'iFit',
                'state' => 'GA',
                'city' => 'Atlanta',
                'zip' => '30302',
                'location_no' => 'I13',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF36'
            ],
            [
                'client' => 'iFit',
                'state' => 'GA',
                'city' => 'Atlanta',
                'zip' => '30303',
                'location_no' => 'I12',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF37'
            ],
            [
                'client' => 'iFit',
                'state' => 'VA',
                'city' => 'Virginia Beach',
                'zip' => '23450',
                'location_no' => 'I11',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF38'
            ],
            [
                'client' => 'iFit',
                'state' => 'VA',
                'city' => 'Virginia Beach',
                'zip' => '23451',
                'location_no' => 'I10',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'IF39'
            ],
        ];

        if (! App::environment(['production', 'staging'])) {
            $locations[] = [
                'client' => 'TruFit Athletic Clubs',
                'name' => 'TruFit 1',
                'state' => 'TX',
                'city' => 'Amarillo',
                'zip' => '79106',
                'location_no' => 'TR66',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST07'
            ];
            $locations[] = [
                'client' => 'TruFit Athletic Clubs',
                'name' => 'TruFit 2',
                'state' => 'TN',
                'city' => 'Antioch',
                'zip' => '37013',
                'location_no' => 'TR77',
                'location_type' => LocationTypeEnum::STORE,
                //'gymrevenue_id' => 'ST07'
            ];
        }

        foreach ($locations as $idx => $location) {
            $client = Client::whereName($location['client'])->first();

            $location['name'] = $location['name'] ?? $location['client'] . " " . ($idx + 1);
            $location['client_id'] = $client->id;
            unset($location['client']);
//            $location['gymrevenue_id'] = GenerateGymRevenueId::run($client->id);
//            $loc_record = Location::whereGymrevenueId($location['gymrevenue_id'])->first();

            $temp_data = Location::factory()
                ->count(1)
                ->make()[0];
            $finalData = array_merge($temp_data->toArray(), $location);
            $finalData['shouldCreateTeam'] = true;

//            if (is_null($loc_record)) {
            VarDumper::dump("Adding {$location['name']}");
            CreateLocation::run($finalData);
//            } else {
//                VarDumper::dump("Skipping {$location['name']}!");
//            }
        }

        if (! App::environment(['production', 'staging']) && !env('RAPID_SEED', false)) {
            ///now do trufit csv import
            VarDumper::dump("Adding TruFit Locations from CSV");
            $key = 'tmp_data/trufit-clubs';
            $csv = file_get_contents(realpath(__DIR__."/../../../database/data/trufit-clubs.csv"));
            Storage::disk('s3')->put($key, $csv);
            $file = Storage::disk('s3')->get($key);

            ImportLocations::run([
                [
                    'key' => $key,
                    'extension' => 'csv',
                    'client_id' => Client::whereName('TruFit Athletic Clubs')->first()->id,
                ],
            ]);
        }
    }
}
