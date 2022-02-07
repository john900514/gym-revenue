<?php

namespace App\Models;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Client;
use App\Models\Clients\ClientDetail;
use App\Models\Clients\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use HasRolesAndAbilities;
    use TwoFactorAuthenticatable;
    use HasRolesAndAbilities;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($user) {
            $current_user = request()->user() ?? $user;
            $client_id = $current_user->currentClientId();
            if ($client_id) {
                $aggy = ClientAggregate::retrieve($client_id);
                $aggy->createUser($user->id, $user->toArray());
                $aggy->persist();
            }
        });

        static::updated(function ($user) {
            $current_user = request()->user() ?? $user;
            $client_id = $current_user->currentClientId();
            if ($client_id) {
                $aggy = ClientAggregate::retrieve($client_id);
                $aggy->updateUser($user->id, ['old' => $user->getOriginal(), 'new' => $user->toArray()]);
                $aggy->persist();
            }
        });

        static::deleted(function ($user) {
            $current_user = request()->user() ?? $user;
            $client_id = $current_user->currentClientId();
            if ($client_id) {
                $aggy = ClientAggregate::retrieve($client_id);
                $aggy->deleteUser($user->id, $user->toArray());
                $aggy->persist();
            }
        });
    }

    /**
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentTeam()
    {
        if (is_null($this->current_team_id) && $this->id) {
            $default_team = $this->default_team()->first();
            $team_record = Team::find($default_team->value);
            $this->switchTeam($team_record);
            //$this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(Jetstream::teamModel(), 'current_team_id');
    }

    public function allLocations()
    {
        return Location::whereClientId($this->currentClientId())->get();
    }

    public function switchLocation($location)
    {
        $this->current_location_id = $location->id;
        return $this->save();
    }

    public function currentClientId()
    {
        $detail = ClientDetail::whereDetail('team')->whereValue($this->current_team_id)->first();
        return is_null($detail) ? null : $detail->client_id;
    }

    public function isClientUser()
    {
        return !is_null($this->associated_client()->first());
    }

    public function client()
    {
        $associated_client = $this->associated_client()->first();
        return Client::find($associated_client);
    }

    public function isCapeAndBayUser()
    {
        return $this->teams()->get()->contains('id', 1);//ID1 = CapeAndBayAdminTeam
    }

    /**
     * If user is AccountOwner of the currentTeam
     * @return bool
     */
    public function isAccountOwner()
    {
        $current_team_id = $this->currentTeam()->first()->id;
        $current_team = $this->teams()->get()->keyBy('id')[$current_team_id] ?? null;
        return $current_team ?  $current_team->pivot->role === 'Account Owner' : false;
    }

    public function details()
    {
        return $this->hasMany('App\Models\UserDetails', 'user_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne('App\Models\UserDetails', 'user_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany('App\Models\Team', 'team_user', 'user_id', 'team_id')->withPivot('role');
    }

    public function default_team()
    {
        return $this->detail()->where('name', '=', 'default_team');
    }

    public function associated_client()
    {
        return $this->detail()->where('name', '=', 'associated_client');
    }

    public function phone()
    {
        return $this->detail()->where('name', '=', 'phone');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })->when($filters['club'] ?? null, function ($query, $club_id) {
            $query->whereHas('teams', function ($query) use ($club_id) {
                return $query->whereHas('detail', function ($query) use ($club_id) {
                    return $query->whereName('team-location')->whereValue($club_id);
                });
            });
        })->when($filters['team'] ?? null, function ($query, $team_id) {
            $query->whereHas('teams', function ($query) use ($team_id) {
                return $query->whereTeamId($team_id);
            });
        });
    }
}
