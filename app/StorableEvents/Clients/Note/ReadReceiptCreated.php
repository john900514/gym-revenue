<?php

declare(strict_types=1);

namespace App\StorableEvents\Clients\Note;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ReadReceiptCreated extends ShouldBeStored
{
    public $user;
    public $data;
    public $client;

    public function __construct(string $client, string $user, array $data)
    {
        $this->client = $client;
        $this->user   = $user;
        $this->data   = $data;
    }
}
