<?php

namespace App\Http\Controllers;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Client;
use App\Models\Clients\ClientDetail;
use App\Models\Clients\Features\ClientService;
use App\Models\Clients\Features\Memberships\TrialMembershipType;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Prologue\Alerts\Facades\Alert;

class ClientSettingsController extends Controller
{

    public function index()
    {
        $user_id = request()->user()->currentClientId();
        $client = Client::with(['details', 'trial_membership_types'])->find($user_id);
        return Inertia::render('ClientSettings/Index', [
            'availableServices' => ClientService::whereClientId($user_id)->get(['feature_name', 'slug', 'id']),
            'services' => $client->services,
            'trialMembershipTypes' => $client->trial_membership_types
        ]);
    }

    public function updateClientServices()
    {
        $data = request()->validate(['services' => ['sometimes', 'array']]);
        $client_id = request()->user()->currentClientId();
        if (array_key_exists('services', $data) && is_array($data['services'])) {
            ClientAggregate::retrieve($client_id)->setClientServices($data['services'], request()->user()->id)->persist();
        }
        Alert::success("Client Services updated.")->flash();

        return Redirect::back();
    }

    public function updateTrialMembershipTypes()
    {
        $data = request()->validate(['trialMembershipTypes' => ['sometimes', 'array']]);
        $client_id = request()->user()->currentClientId();
        if (array_key_exists('trialMembershipTypes', $data) && is_array($data['trialMembershipTypes'])) {
            $trialMembershipTypesToUpdate = collect($data['trialMembershipTypes'])->filter(function ($t) {
                return $t['id'] !== null;
            });
            $trialMembershipTypesToCreate = collect($data['trialMembershipTypes'])->filter(function ($t) {
                return $t['id'] === null;
            });

            $client_aggy = ClientAggregate::retrieve($client_id);

            foreach ($trialMembershipTypesToUpdate as $trialMembershipTypeData) {
                $client_aggy->updateTrialMembershipType($trialMembershipTypeData, request()->user()->id);
            }
            foreach ($trialMembershipTypesToCreate as $trialMembershipTypeData) {
                $client_aggy->createTrialMembershipType($trialMembershipTypeData, request()->user()->id);
            }
            $client_aggy->persist();
        }
        Alert::success("Trial Membership Types updated.")->flash();

        return Redirect::back();
    }
}
