<?php

namespace Database\Seeders\Clients;

use App\Domain\Clients\Actions\CreateClient;
use App\Enums\ClientServiceEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('RAPID_SEED') === true) {
            $clients = [
                'The Kalamazoo' => 1,
                ];
        } else {
            $clients = [
                'The Kalamazoo' => 1,
                'iFit' => 1,
                'Stencils' => 1,
                'The Z' => 1,
                'Sci-Fi Purple Gyms' => 1,
                'FitnessTruth' => 1,
            ];
        }


        if (! App::environment(['production', 'staging']) && ! env('RAPID_SEED', false)) {
            $clients['TruFit Athletic Clubs'] = 1;
        }


        foreach ($clients as $name => $active) {
            $client = CreateClient::run(
                [
                    'name' => $name,
                    'active' => $active,
                    'services' => collect(ClientServiceEnum::cases())->map(fn ($e) => $e->name),
                ]
            );
        }
    }
}
