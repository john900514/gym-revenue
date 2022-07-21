<?php

namespace App\Domain\Campaigns\ScheduledCampaigns\Events;

use App\Domain\Campaigns\ScheduledCampaigns\ScheduledCampaign;
use App\StorableEvents\EntityDeleted;

class ScheduledCampaignDeleted extends EntityDeleted
{
    protected function getEntity(): string
    {
        return ScheduledCampaign::class;
    }
}