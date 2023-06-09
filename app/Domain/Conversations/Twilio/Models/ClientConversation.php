<?php

declare(strict_types=1);

namespace App\Domain\Conversations\Twilio\Models;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?User $user
 * @method static create(array $payload): ClientVoiceCallLog
 */
class ClientConversation extends Model
{
    use HasFactory;

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var array<string>  */
    protected $fillable = [
        'id',
        'client_id',
        'user_id',
        'conversation_id',
        'gateway_provider_id',
        'user_conversation_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
