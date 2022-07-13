<?php

namespace App\Domain\Clients\Events;

use App\Domain\Clients\Models\Client;
use App\StorableEvents\EntityCreated;

class ClientOwnedEntityCreated extends EntityCreated
{
    protected function getEntity(): string
    {
        return Client::class;
    }
}
