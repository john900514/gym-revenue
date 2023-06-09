<?php

declare(strict_types=1);

namespace App\Imports;

use App\Domain\Clients\Projections\Client;
use App\Domain\Users\Actions\CreateUser;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Silber\Bouncer\Database\Role;

class UsersImport implements ToCollection
{
    protected string $client_id;

    public function __construct(string $client)
    {
        $this->client_id = $client;
    }

    public function collection(Collection|\Illuminate\Support\Collection $rows): void
    {
        $client   = Client::with('teams')->find($this->client_id);
        $roles    = Role::whereScope($this->client_id)->whereTitle('Employee')->first();
        $team_ids = $client->teams()->pluck('id');

        foreach ($rows as $row) {
            CreateUser::run([
                'first_name' => $row[0],
                'last_name' => $row[1],
                'email' => $row[2],
                'password' => 'Hello123!',
                'team_ids' => $team_ids,
                'role_id' => $roles->id,
                'home_club' => null,
                'client_id' => $this->client_id,
            ]);
        }
    }
}
