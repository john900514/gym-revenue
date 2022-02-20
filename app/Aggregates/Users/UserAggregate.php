<?php

namespace App\Aggregates\Users;

use App\Models\Clients\Client;
use App\StorableEvents\Users\Activity\Impersonation\UserImpersonatedAnother;
use App\StorableEvents\Users\Activity\Impersonation\UserStoppedBeingImpersonated;
use App\StorableEvents\Users\Activity\Impersonation\UserStoppedImpersonatedAnother;
use App\StorableEvents\Users\Activity\Impersonation\UserWasImpersonated;
use App\StorableEvents\Users\UserAddedToTeam;
use App\StorableEvents\Users\UserCreated;
use App\StorableEvents\Users\UserDeleted;
use App\StorableEvents\Users\UserUpdated;
use App\StorableEvents\Users\WelcomeEmailSent;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class UserAggregate extends AggregateRoot
{
    protected $client_id = '';
    protected $teams = [];
    protected array $activity_history = [];

    public function applyNewUser(UserCreated $event)
    {
        // @todo - put something useful here
    }

    public function applyUserAddedToTeam(UserAddedToTeam $event)
    {
        $this->teams[$event->team] = [
            'team_id' => $event->team,
            'team_name' => $event->name,
            'client_id' => $event->client
        ];
    }

    public function applyUserImpersonatedAnother(UserImpersonatedAnother $event)
    {
        $this->activity_history[] = [
            'event' => 'user-started-impersonating',
            'user_impersonated' => $event->victim,
            'date' => $event->date,
        ];
    }
    public function applyUserStoppedImpersonatedAnother(UserStoppedImpersonatedAnother $event)
    {
        $this->activity_history[] = [
            'event' => 'user-stopped-impersonating',
            'user_impersonated' => $event->victim,
            'date' => $event->date,
        ];
    }

    public function applyUserWasImpersonated(UserWasImpersonated $event)
    {
        $this->activity_history[] = [
            'event' => 'user-was-impersonated',
            'impersonating_user' => $event->invader,
            'date' => $event->date,
        ];
    }
    public function applyUserStoppedBeingImpersonated(UserStoppedBeingImpersonated $event)
    {
        $this->activity_history[] = [
            'event' => 'user-stopped-being-impersonated',
            'impersonating_user' => $event->invader,
            'date' => $event->date,
        ];
    }


    public function createUser(string $created_by_user_id, array $payload)
    {
        $this->recordThat(new UserCreated($this->uuid(), $created_by_user_id, $payload));
        return $this;
    }

    public function deleteUser(string $deleted_by_user_id, array $payload)
    {
        $this->recordThat(new UserDeleted($this->uuid(), $deleted_by_user_id, $payload));
        return $this;
    }

    public function updateUser(string $updated_by_user_id, array $payload)
    {
        $this->recordThat(new UserUpdated($this->uuid(), $updated_by_user_id, $payload));
        return $this;
    }

    public function sendWelcomeEmail()
    {
        // @todo - logic to throw an exception if the user is active
        $this->recordThat(new WelcomeEmailSent($this->uuid()));
        return $this;
    }

    public function addUserToTeam(string $team_id, $team_name, $client_id = null)
    {
        if(array_key_exists($team_id, $this->teams))
        {
            // @todo - make an exception to throw here that the user is already a member
        }

        $this->recordThat(new UserAddedToTeam($this->uuid(), $team_id, $team_name, $client_id));

        return $this;
    }

    public function getTeams()
    {
        $results = $this->teams;

        $client = new Client();
        foreach($results as $idx => $team)
        {
            if(!is_null($team['client_id']) && (($client->id ?? null) != $team['client_id']))
            {
                $client = $client->find($team['client_id']);
            }

            $client_name = $client->name ?? 'GymRevenue';
            $results[$idx]['client_name'] = $client_name;
        }

        return $results;
    }

    public function  activateUserImpersonationMode($victim_id)
    {
        $this->recordThat(new UserImpersonatedAnother($this->uuid(), $victim_id, date('Y-m-d H:i:s')));
        return $this;
    }

    public function  deactivateUserImpersonationMode($liberated_id)
    {
        $this->recordThat(new UserStoppedImpersonatedAnother($this->uuid(), $liberated_id, date('Y-m-d H:i:s')));
        return $this;
    }

    public function activatePossessionMode($invader_id)
    {
        $this->recordThat(new UserWasImpersonated($this->uuid(), $invader_id, date('Y-m-d H:i:s')));
        return $this;
    }

    public function deactivatePossessionMode($coward_id)
    {
        $this->recordThat(new UserStoppedBeingImpersonated($this->uuid(), $coward_id, date('Y-m-d H:i:s')));
        return $this;
    }
}