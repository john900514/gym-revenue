<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Home;

use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\Domain\Campaigns\ScheduledCampaigns\ScheduledCampaign;
use App\Domain\Clients\Projections\Client;
use App\Domain\Locations\Projections\Location;
use App\Support\CurrentInfoRetriever;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDashboardWidgets
{
    use AsAction;

    public function handle(): array
    {
        $results = [];

        $team = CurrentInfoRetriever::getCurrentTeam();
        if ($team->client !== null) {
            $num_locs                = 0;
            $num_scheduled_campaigns = ScheduledCampaign::whereIn('status', ['PENDING', 'ACTIVE'])->count();
            $num_drip_campaigns      = DripCampaign::whereIn('status', ['PENDING', 'ACTIVE'])->count();
            $last_widget_count       = 0;
            if ($team->home_team) {
                $num_locs = Location::whereActive(1)->count();
            } else {
                // get the locations the active team has access to
                $num_locs = sizeof($team->locations());
            }

            /** @todo - find the context
             * 1. Default Team shows
             *      -- Total Locations
             *      -- Active Email Campaigns
             *      -- Active SMS Campaigns
             *      -- Today's Sales
             *
             * 2. Team Home
             *      -- Today's Leads
             *      -- Total Leads
             *      -- Total Team Members
             *      -- Total Locations
             *
             * 3. Team Sales Rep
             *      -- Today's Hot New Leads
             *      -- Total Leads
             *      -- Leads Assigned to You
             *      -- Leads Needing Your Attention
             */
            $results = [
                [
                    'title' => 'Total Locations',
                    'value' => $num_locs,
                    'type' => 'warning',
                    'icon' => 'users',
                ],
                [
                    'title' => 'Active Scheduled Campaigns',
                    'value' => $num_scheduled_campaigns,
                    'type' => 'success',
                    'icon' => 'money',
                ],
                [
                    'title' => 'Active Drip Campaigns',
                    'value' => $num_drip_campaigns,
                    'type' => 'info',
                    'icon' => 'cart',
                ],
                [
                    'title' => "Today's Sales",
                    'value' => "$ 0",
                    'type' => 'danger',
                    'icon' => 'message',
                ],
            ];
        } else {
            $clients = Client::all();

            $results = [
                [
                    'title' => 'Total Clients',
                    'value' => count($clients),
                    'type' => 'warning',
                    'icon' => 'users',
                ],
                [
                    'title' => 'Total Revenue Funneled',
                    'value' => "$ 0",
                    'type' => 'success',
                    'icon' => 'money',
                ],
                [
                    'title' => 'Total Profits',
                    'value' => "$ 0",
                    'type' => 'info',
                    'icon' => 'cart',
                ],
                [
                    'title' => 'Total MCU Films',
                    'value' => "28",
                    'type' => 'danger',
                    'icon' => 'message',
                ],

            ];
        }

        return $results;
    }
}
