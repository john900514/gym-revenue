<?php

namespace App\Domain\Users\Models;

use App\Domain\Clients\Models\Client;
use App\Domain\Teams\Models\Team;
use App\Enums\SecurityGroupEnum;
use App\Models\Clients\Location;
use App\Models\File;
use App\Models\Position;
use App\Models\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable
{
    use HasTeams;
    use HasFactory;
    use Notifiable;
    use Impersonate;
    use HasApiTokens;
    use HasProfilePhoto;
    use HasRolesAndAbilities;
    use TwoFactorAuthenticatable;
    use Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'email', 'alternate_email', 'first_name', 'last_name',
        'address1', 'address2', 'city', 'state', 'zip', 'phone',
        'manager', 'home_location_id', 'start_date', 'end_date',
        'termination_date',
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
        'access_token',
        'client_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_cape_and_bay_user' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url', 'name',
    ];

    /**
     * Determine if the user belongs to the given team.
     *
     * @param  mixed  $team
     * @return bool
     */
    public function belongsToTeam($team)
    {
        if (is_null($team)) {
            return false;
        }

//        if($this->ownsTeam($team)){
//            return true;
//        }
        return $this->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });
    }

    public function allLocations()
    {
        return Location::whereClientId($this->client_id)->get();
    }

    public function switchLocation($location)
    {
        $this->current_location_id = $location->id;

        return $this->save();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isClientUser()
    {
        return $this->client_id !== null;
    }

    /**
     * If user is an AccountOwner of the currentClient
     * @return bool
     */
    public function isAccountOwner()
    {
        return $this->inSecurityGroup(SecurityGroupEnum::ACCOUNT_OWNER);
    }

    /**
     * If user is an AccountOwner of the currentClient
     * @return bool
     */
    public function isAdmin()
    {
        return $this->inSecurityGroup(SecurityGroupEnum::ADMIN);
    }

    public function details()
    {
        return $this->hasMany('App\Domain\Users\Models\UserDetails', 'user_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne('App\Domain\Users\Models\UserDetails', 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'user_id', 'id');
    }

    public function contact_preference()
    {
        return $this->detail()->where('name', '=', 'contact_preference');
    }

    public function notes()
    {
        return $this->hasMany('App\Models\Note', 'entity_id')->whereEntityType(self::class);
    }

    public function teams()
    {
        return $this->belongsToMany('App\Domain\Teams\Models\Team', 'team_user', 'user_id', 'team_id');

        return $teams;
    }

    public function default_team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'default_team_id', 'id');
    }

    public function api_token()
    {
        return $this->detail()->where('name', '=', 'api-token');
    }

    public function column_config()
    {
        return $this->details()->where('name', '=', 'column-config');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address1', 'like', '%' . $search . '%')
                    ->orWhere('address2', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('state', 'like', '%' . $search . '%')
                    ->orWhere('zip', 'like', '%' . $search . '%');
            });
        })->when($filters['club'] ?? null, function ($query, $location_id) {
            /*$query->whereHas('teams', function ($query) use ($location_id) {
                return $query->whereHas('detail', function ($query) use ($location_id) {
                    return $query->whereName('team-location')->whereValue($location_id);
                });
            });*/
            //This returns home club location instead of the above clubs a user is a part of.
            $query->where(function ($query) use ($location_id) {
                $query->where('home_location_id', '=', $location_id);
            });
        })->when($filters['team'] ?? null, function ($query, $team_id) {
            $query->whereHas('teams', function ($query) use ($team_id) {
                return $query->whereTeamId($team_id);
            });
        })->when($filters['roles'] ?? null, function ($query, $role) {
            $query->join('assigned_roles', function ($join) use ($role) {
                $join->on('users.id', '=', 'assigned_roles.entity_id')
                    ->where('assigned_roles.role_id', '=', $role);
            })->get();
        });
    }

    public function role()
    {
        return $this->roles[0] ?? null;
    }

    public function getRole()
    {
        return $this->getRoles()[0] ?? null;
//        if(!$roles || !count($roles)){
//            return null;
//        }
//        return $roles[0];
    }

    public function securityGroup()
    {
        $role = $this->role();
        if (! $role) {
            return null;
        }

        return SecurityGroupEnum::from($role->group);
    }

    public function inSecurityGroup(SecurityGroupEnum ...$groups)
    {
        return in_array($this->securityGroup(), $groups);
    }

    public function isAtLeastSecurityGroup(SecurityGroupEnum $group)
    {
        return $this->securityGroup()->value <= $group->value;
    }

    public function notifications()
    {
        return $this->hasMany('notifications');
    }

    public function home_location()
    {
        return $this->belongsTo(Location::class, 'home_location_id', 'gymrevenue_id');
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getIsManagerAttribute()
    {
        return $this->manager !== null && $this->manager !== '';
    }

    public function departments()
    {
        return $this->belongsToMany(\App\Domain\Departments\Department::class, 'user_department', 'user_id', 'department_id');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'user_position', 'user_id', 'position_id');
    }

    /**

     * Get all of the teams the user owns or belongs to.

     *

     * @return \Illuminate\Support\Collection

     */
    public function allTeams()
    {
        return $this->teams->sortBy('name');
    }
}