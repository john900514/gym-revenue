<?php

namespace App\Aggregates\Clients;

use App\StorableEvents\Clients\Files\FileCreated;
use App\StorableEvents\Clients\Files\FileDeleted;
use App\StorableEvents\Clients\Files\FilePermissionsUpdated;
use App\StorableEvents\Clients\Files\FileRenamed;
use App\StorableEvents\Clients\Files\FileRestored;
use App\StorableEvents\Clients\Files\FileReplaced;
use App\StorableEvents\Clients\Files\FileTrashed;
use App\StorableEvents\Clients\Files\FileUpdated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class FileAggregate extends AggregateRoot
{

    protected string $default_team = '';
    protected array $teams = [];

    protected static bool $allowConcurrency = true;

    public function create(string $userId, array $data)
    {
        $this->recordThat(new FileCreated($userId, $data));
        return $this;
    }

    public function updatePermissions(string $userId, $data)
    {
        $this->recordThat(new FilePermissionsUpdated($userId, $data));
        return $this;
    }

    public function trash(string $userId)
    {
        $this->recordThat(new FileTrashed($userId, $this->uuid()));
        return $this;
    }

    public function restore(string $userId)
    {
        $this->recordThat(new FileRestored($userId, $this->uuid()));
        return $this;
    }

    public function delete(string $userId, array $data)
    {
        $this->recordThat(new FileDeleted($userId, $this->uuid(), $data));
        return $this;
    }

    public function rename(string $userId, $data)
    {
        $this->recordThat(new FileRenamed($userId, $data));
        return $this;
    }
}
