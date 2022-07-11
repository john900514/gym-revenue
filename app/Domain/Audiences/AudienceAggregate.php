<?php

namespace App\Domain\Audiences;

use App\Domain\Audiences\Events\AudienceCreated;
use App\Domain\Audiences\Events\AudienceTrashed;
use App\Domain\Audiences\Events\AudienceUpdated;
use App\Domain\Audiences\Events\PositionDeleted;
use App\Domain\Audiences\Events\PositionRestored;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class AudienceAggregate extends AggregateRoot
{
    public function create(array $payload): static
    {
        $this->recordThat(new AudienceCreated($payload));

        return $this;
    }

    public function trash(): static
    {
        $this->recordThat(new AudienceTrashed());

        return $this;
    }

    public function restore(): static
    {
        $this->recordThat(new PositionRestored());

        return $this;
    }

    public function delete(): static
    {
        $this->recordThat(new PositionDeleted());

        return $this;
    }

    public function update(array $payload): static
    {
        $this->recordThat(new AudienceUpdated($payload));

        return $this;
    }
}
