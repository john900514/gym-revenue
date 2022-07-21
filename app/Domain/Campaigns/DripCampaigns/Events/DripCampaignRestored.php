<?php

namespace App\Domain\Campaigns\DripCampaigns\Events;

use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\StorableEvents\EntityRestored;

class DripCampaignRestored extends EntityRestored
{
    protected function getEntity(): string
    {
        return DripCampaign::class;
    }
}