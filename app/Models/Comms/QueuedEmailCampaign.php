<?php

declare(strict_types=1);

namespace App\Models\Comms;

use App\Models\Clients\Features\EmailCampaigns;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QueuedEmailCampaign extends Model
{
    use Uuid;

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $primaryKey = 'id';

    /** @var string */
    protected $keyType = 'string';

    /** @var array<string> */
    protected $fillable = [
        'id',
        'email_campaign_id',
        'trigger_at',
        'started_at',
        'completed_at',
    ];

    public function campaign(): HasOne
    {
        return $this->hasOne(EmailCampaigns::class, 'id', 'email_campaign_id');
    }
}
