<?php

declare(strict_types=1);

namespace App\Domain\Teams\Events;

use App\Domain\Teams\Models\Team;
use App\StorableEvents\EntityUpdated;

class TeamUpdated extends EntityUpdated
{
    public function getEntity(): string
    {
        return Team::class;
    }
}
