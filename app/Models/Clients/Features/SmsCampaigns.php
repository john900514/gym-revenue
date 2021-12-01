<?php

namespace App\Models\Clients\Features;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\User;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class SmsCampaigns extends Model
{
    use Notifiable, SoftDeletes, Uuid;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name', 'active', 'client_id', 'team_id', 'created_by_user_id'
    ];

    protected static function booted()
    {
        static::created(function($campaign) {

            if(!is_null($campaign->client_id))
            {
                // use the client aggy to do a created details record
                ClientAggregate::retrieve($campaign->client_id)
                    ->createNewSMSCampaign($campaign->id,$campaign->created_by_user_id)
                    ->persist();
            }
            else
            {
                // make cnb created details record
                $detail = SmsCampaignDetails::create([
                    'sms_campaign_id' => $campaign->id,
                    'detail' => 'created',
                    'value' => $campaign->created_by_user_id,
                ]);
                if($campaign->created_by_user_id == 'auto')
                {
                    $detail->misc = ['msg' => 'Template was auto-generated'];
                }
                else
                {
                    $user = User::find($campaign->created_by_user_id);
                    $detail->misc = ['msg' => 'Template was created by '.$user->name.' on '.date('Y-m-d')];
                }
            }
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('created_by_user_id', 'like', '%' . $search . '%')
                ;
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by_user_id');
    }
}
