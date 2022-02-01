<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;

class TeamUser extends Model
{
    use HasFactory;

    protected $table = 'team_user';

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamUserCreated::class,
        'updated' => TeamUserUpdated::class,
        'deleted' => TeamUserDeleted::class,
    ];

    public function teams()
    {
        return $this->hasMany('App\Models\Team', 'team_id', 'id');
    }

}