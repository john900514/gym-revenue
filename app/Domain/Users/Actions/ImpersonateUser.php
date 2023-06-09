<?php

declare(strict_types=1);

namespace App\Domain\Users\Actions;

use App\Aggregates\Clients\ClientAggregate;
use App\Domain\Teams\Models\Team;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\User;
use App\Enums\SecurityGroupEnum;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

use function auth;
use function redirect;
use function request;
use function response;

class ImpersonateUser
{
    use AsAction {
        __invoke as protected invokeFromLaravelActions;
    }

    public function rules(): array
    {
        return [
            'victimId' => 'bail|required|exists:users,id',
        ];
    }

    public function handle(): bool
    {
        $results = false;
        $data    = request()->all();

        $invader = auth()->user();
        if ($invader->inSecurityGroup(SecurityGroupEnum::ADMIN)) {
            $victim = User::withoutGlobalScopes()->findOrFail($data['victimId']);
            $team   = Team::withoutGlobalScopes()->findOrFail($victim->default_team_id);
        } else {
//            $victim = User::findOrFail($data['victimId']);
            $victim = User::with('defaultTeam')->findOrFail($data['victimId']);
            $team   = $victim->defaultTeam;
//            $team = Team::findOrFail($victim->default_team_id);
        }

        if ($invader->can('users.impersonate', User::class)) {
            auth()->user()->impersonate($victim);
            //TODO: ensure that this is setting session of impersonator, so it doesn't leak over into victim
            //TODO: we should be setting to the team in the impersonation window.
            session()->put('current_team_id', $team->id);
            session()->put(
                'current_team',
                [
                    'id' => $team->id,
                    'name' => $team->name,
                    'client_id' => $team->client_id,
                ]
            );
            session()->put('client_id', $team->client_id);
            session()->put('user_id', $victim->id);
            $results = true;
        }

        // tattle on this user being sneaky in its own aggy
        UserAggregate::retrieve($invader->id)->activateUserImpersonationMode($victim->id)->persist();
        // tattle on this user hopefully running support in the "victim's" aggy
        UserAggregate::retrieve($victim->id)->activatePossessionMode($invader->id)->persist();

        // rat on this user to the paying customer - the client (aggy)
        if ($victim->client !== null) {
            ClientAggregate::retrieve($victim->client->id)
                ->logImpersonationModeActivity($victim->id, $invader->id)->persist();
        }

        return $results;
    }

    public function jsonResponse($result)
    {
        $results = false;
        $code    = 500;

        if ($result) {
            $results = true;
            $code    = 200;
        }

        return response($results, $code);
    }

    public function htmlResponse($result): RedirectResponse
    {
        if ($result) {
            Alert::info('You are now in impersonation mode! Disable it in the name dropdown or the bottom of the screen!')->flash();

            return redirect()->route('dashboard');
        }

        Alert::error('Error. Impersonation mode not active.')->flash();

        return redirect()->back();
    }
}
