<?php

namespace App\Http\Controllers;

use App\Domain\Clients\Models\Client;
use App\Domain\Teams\Models\Team;
use App\Enums\SecurityGroupEnum;
use App\Services\Dashboard\HomeDashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected Client $clients;
    protected HomeDashboardService $service;

    public function __construct(Client $clients, HomeDashboardService $service)
    {
        $this->clients = $clients;
        $this->service = $service;
    }

    public function index()
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        $client = $team->client;
        $announcements = [];
        $team_name = $team->name;
        $vue = 'Dashboard';

        if ($client !== null) {
            $clients = collect([$client]);
            $account = $client->name;
            $widgets = $this->service->getDashboardWidgets();
            if ($user->inSecurityGroup(SecurityGroupEnum::ADMIN, SecurityGroupEnum::ACCOUNT_OWNER, SecurityGroupEnum::REGIONAL_ADMIN)) {
                $vue = 'Dashboards/AccountAdminDashboard';
            } elseif ($user->inSecurityGroup(SecurityGroupEnum::LOCATION_MANAGER)) {
                $vue = 'Dashboards/LocationManagerDashboard';
            } else {
                $vue = 'Dashboards/SalesRepDashboard';
            }

            return Inertia::render($vue, [
                'teamName' => $team_name,
                'clients' => $clients,
                'accountName' => $account,
                'widgets' => $widgets,
                'announcements' => $announcements,
            ]);
        } else {
            $account = 'GymRevenue';
            $clients = $this->clients->all();
            $widgets = $this->service->getDashboardWidgets();
            $announcements = $this->service->getAppStateAnnouncements();

            // Check if this is the CnB Default team.
            // If so the vue will be AdminDashboard. else DeveloperDashboard
            $vue = ($team->home_team) ? 'Dashboards/AdminDashboard' : 'Dashboards/DeveloperDashboard';
            $teams = $user->allTeams()->load('client');

            if (count($clients) > 0) {
                $clients = $clients->toArray();
                foreach ($clients as $idx => $client) {
                    $clients[$idx]['created_at'] = date('M d, Y', strtotime($client['created_at']));
                    $clients[$idx]['updated_at'] = date('M d, Y', strtotime($client['updated_at']));
                }
            }

            return Inertia::render($vue, [
                'teamName' => $team_name,
                'teams' => $teams,
                'clients' => $clients,
                'accountName' => $account,
                'widgets' => $widgets,
                'announcements' => $announcements,
            ]);
        }
    }
}
