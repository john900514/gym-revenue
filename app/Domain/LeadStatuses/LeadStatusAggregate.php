<?php

namespace App\Domain\LeadStatuses;

use App\Domain\LeadStatuses\Events\LeadStatusCreated;
use App\Domain\LeadStatuses\Events\LeadStatusUpdated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class LeadStatusAggregate extends AggregateRoot
{
    public function create(array $payload)
    {
        $this->recordThat(new LeadStatusCreated($payload));

        return $this;
    }

    public function update(array $payload)
    {
        $this->recordThat(new LeadStatusUpdated($payload));

        return $this;
    }
}