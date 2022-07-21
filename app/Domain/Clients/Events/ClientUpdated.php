<?php

namespace App\Domain\Clients\Events;

use App\Domain\Clients\Models\Client;
use App\StorableEvents\EntityUpdated;

class ClientUpdated extends EntityUpdated
{
    protected function getEntity(): string
    {
        return Client::class;
    }
}