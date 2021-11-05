<?php

namespace App\Models;

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

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

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
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentTeam()
    {
        if (is_null($this->current_team_id) && $this->id) {
            $this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(Jetstream::teamModel(), 'current_team_id');
    }

    public function allLocations()
    {
        return Location::whereClientId($this->currentClientId())->get();
    }

    public function switchLocation($location){
        $this->current_location_id = $location->id;
        return $this->save();
    }

    public function currentClientId()
    {
        $detail = ClientDetail::whereDetail('team')->whereValue($this->current_team_id)->first();
        return is_null($detail) ? null : $detail->client_id;
//        return $this->details()->whereName('associated_client')->first();
    }

    public function details()
    {
        return $this->hasMany('App\Models\UserDetails', 'user_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne('App\Models\UserDetails', 'user_id', 'id');
    }

}
