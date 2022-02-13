<?php

namespace App\Aggregates\Clients\Traits\Actions;

use App\StorableEvents\Shared\UserCreated;
use App\StorableEvents\Shared\UserDeleted;
use App\StorableEvents\Shared\UserUpdated;

trait ClientUserActions
{

    public function createUser(string $created_by_user_id, array $payload)
    {
        $this->recordThat(new UserCreated($this->uuid(), $created_by_user_id, $payload));
        return $this;
    }

    public function deleteUser(string $deleted_by_user_id, array $payload)
    {
        $this->recordThat(new UserDeleted($this->uuid(), $deleted_by_user_id, $payload));
        return $this;
    }

    public function updateUser(string $updated_by_user_id, array $payload)
    {
        $this->recordThat(new UserUpdated($this->uuid(), $updated_by_user_id, $payload));
        return $this;
    }
}
