<?php

declare(strict_types=1);

namespace App\StorableEvents;

//WARNING: YOU CANNOT CHANGE THE SHAPE OF STORED EVENTS ONCE THE APP
//         IS IN PROD.  OLD EVENTS WILL BE INCOMPATIBLE AND WILL BREAK
abstract class EntityTrashed extends GymRevCrudEvent
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getOperation(): string
    {
        return "TRASHED";
    }
}
