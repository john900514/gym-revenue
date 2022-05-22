<?php

namespace App\StorableEvents\Clients\Locations;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class LocationCreated extends ShouldBeStored
{
    //user = request's current_user
    public $client;
    public $user;
    public $payload;

    public function __construct(string $client, string $user, array $payload)
    {
        $this->client = $client;
        $this->user = $user;
        $this->payload = $payload;
    }
}
