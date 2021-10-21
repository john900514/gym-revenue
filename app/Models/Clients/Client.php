<?php

namespace App\Models\Clients;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use Notifiable, SoftDeletes, Uuid;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'active',
    ];

    public function details()
    {
        return $this->hasMany('App\Models\Clients\ClientDetail', 'client_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne('App\Models\Clients\ClientDetail', 'client_id', 'id');
    }
}
