<?php

namespace App\StorableEvents\Clients\Activity\Campaigns;

class AudienceAssignedToSmsCampaign extends AudienceAssignedToEmailCampaign
{
    public $client;
    public $audience;
    public $campaign;
    public $user;

    public function __construct(string $client, string $audience, string $campaign, string $user)
    {
        $this->client = $client;
        $this->audience = $audience;
        $this->campaign = $campaign;
        $this->user = $user;
    }
}
