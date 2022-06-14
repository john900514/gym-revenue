<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveReportsByDay extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = ['id', 'client_id', 'location_id', 'date', 'entity', 'value'];

    protected $casts = [
        'value' => 'float',
        'date' => 'date',
    ];
}