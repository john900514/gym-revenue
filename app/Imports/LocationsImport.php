<?php

declare(strict_types=1);

namespace App\Imports;

use App\Domain\Locations\Actions\CreateLocation;
use App\Domain\Locations\Enums\LocationType;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LocationsImport implements ToCollection
{
    protected string $client_id;

    public function __construct(string $client)
    {
        $this->client_id = $client;
    }

    public function collection(Collection|\Illuminate\Support\Collection $rows): void
    {
        foreach ($rows as $row) {
            CreateLocation::run([
                'client_id' => $this->client_id,
                'name' => $row[0],
                'location_no' => $row[1],
                'city' => $row[2],
                'state' => $row[3],
                'zip' => $row[4],
                'address1' => $row[5],
                'shouldCreateTeam' => true,
                'location_type' => LocationType::STORE,
                'capacity' => $row[6],
            ]);
        }
    }
}
