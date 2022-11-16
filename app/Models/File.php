<?php

namespace App\Models;

use App\Domain\Clients\Projections\Client;
use App\Models\Traits\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Location
 *
 * @mixin Builder
 */
class File extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Sortable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'client_id', 'user_id', 'filename', 'original_filename', 'extension', 'bucket', 'url', 'key', 'size', 'permissions', 'folder', 'entity_type', 'entity_id', 'visibility', 'hidden', 'type']; //'deleted_at'

    protected $casts = [
        'permissions' => 'array',
        'visibility' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getUrlAttribute()
    {
        if ($this->visibility) {
            return Storage::disk('s3')->temporaryUrl($this->original['key'], now()->addMinutes(10));
        } else {
            return $this->original['url'];
        }
    }



//    public function details()
//    {
//        return $this->hasMany(FileDetails::class);
//    }
//
//    public function detail()
//    {
//        return $this->hasOne(FileDetails::class);
//    }

    public function scopeFilter($query, array $filters)
    {
        $client_id = request()->user()->client_id;
        $security_group = request()->user()->securityGroup();

        $query->whereClientId($client_id)
        // ->whereUserId(null)
        ->whereHidden(false)
        ->whereEntityType(null)
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('filename', 'like', '%' . $search . '%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
