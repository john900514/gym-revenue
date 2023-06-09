<?php

declare(strict_types=1);

namespace App\StorableEvents;

use App\Domain\Clients\Projections\Client;
use App\Domain\Clients\Projections\ClientActivity;
use App\Domain\Users\Models\User;
use App\Support\CurrentInfoRetriever;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

class GymRevStoredEvent extends EloquentStoredEvent
{
    public function operation(): ?string
    {
        return $this->meta_data['operation'] ?? null;
    }

    //TODO: use enums for the meta_data keys
    public function autoGenerated(): bool
    {
//        return $this->meta_data[meta_data::STORED_EVENT_ID] ?? null;
        return $this->meta_data['auto-generated'] ?? false;
    }

    public function userId(): ?string
    {
        return $this->meta_data['user_id'] ?? null;
    }

    public function accessToken(): ?string
    {
        return $this->meta_data['access_token'] ?? null;
    }

    public function ipAddress(): ?string
    {
        return $this->meta_data['ip-address'] ?? null;
    }

    public function createdAt(): ?CarbonImmutable
    {
        return CarbonImmutable::make($this->metaData[MetaData::CREATED_AT] ?? null);
    }

    public function entity(): ?string
    {
        return $this->meta_data['entity'] ?? null;
    }

    public function entityId(): ?string
    {
        return $this->aggregate_uuid;
    }

    public function clientId(): ?string
    {
        if ($this->entity() === Client::class) {
            return $this->entityId();
        }

        return $this->meta_data['client_id'];
    }

    public function impersonatorUserId(): ?string
    {
        return $this->meta_data['impersonator_user_id'] ?? null;
    }

    public function impersonationActive(): bool
    {
        return $this->impersonatorUserId() !== null;
    }

    public function impersonatorUser(): ?User
    {
        if ($this->impersonationActive()) {
            return User::withoutGlobalScopes()->find($this->impersonatorUserId());
        }

        return null;
    }

    //we could potentially use attributes instead of function for clientId, and then in turn
    //setup a relation with Client using that attribute.
    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function user(): ?User
    {
        $userId = $this->userId();
        if (! $userId) {
            return null;
        }

        return User::withoutGlobalScopes()->find($userId);
    }

    public function apiUser(): ?User
    {
        $accessToken = $this->accessToken();
        if (! $accessToken) {
            return null;
        }

        return User::withoutGlobalScopes()->whereAccessToken($accessToken)->first();
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (GymRevStoredEvent $storedEvent): void {
            if (! Projectionist::isReplaying()) {
                $user_id = null;
                if (session('user_id')) {
                    $user_id = auth()->user()->id;
                }
                $client_id    = CurrentInfoRetriever::getCurrentClientID();
                $access_token = request()->bearerToken() ?? null;
                $ip           = request()->ip() ?? null;
                $api_user     = $access_token !== null ? User::whereAccessToken($access_token)->first() : null;
                $api_user_id  = $api_user->id ?? null;
                if (! $client_id && $api_user && $api_user->client_id) {
                    $client_id = $api_user->client_id;
                }
                if (! $client_id) {
                    if (array_key_exists('client', $storedEvent->event_properties)) {
                        $client_id = $storedEvent->event_properties['client'];
                    } elseif (array_key_exists('payload', $storedEvent->event_properties) && array_key_exists('client_id', $storedEvent->event_properties['payload'])) {
                        $client_id = $storedEvent->event_properties['payload']['client_id'];
                    }
                }

                $impersonatorUserId                             = app('impersonate')->getImpersonatorId();
                $storedEvent->meta_data['impersonator_user_id'] = $impersonatorUserId;


                $auto_generated = $user_id === null && $access_token === null;

                $storedEvent->meta_data['client_id']      = $client_id;
                $storedEvent->meta_data['user_id']        = $user_id;
                $storedEvent->meta_data['api_user_id']    = $api_user_id;
                $storedEvent->meta_data['access_token']   = $access_token;
                $storedEvent->meta_data['auto-generated'] = $auto_generated;
                $storedEvent->meta_data['ip-address']     = $ip;
            }
        });
        static::created(function (GymRevStoredEvent $storedEvent): void {
            if ($storedEvent->clientId() && $storedEvent->entity()) {
                (new ClientActivity())->writeable()->create([
                    'stored_event_id' => $storedEvent->id,
                    'client_id' => $storedEvent->clientId(),
                    'entity' => $storedEvent->entity(),
                    'entity_id' => $storedEvent->entityId(),
                    'operation' => $storedEvent->operation(),
                    'user_id' => $storedEvent->userId(),
                    'api_user_id' => $storedEvent->accessToken() ? $storedEvent->apiUser()->id : null,
                    'access_token' => $storedEvent->accessToken(),
                    'ip_address' => $storedEvent->ipAddress(),
                    'created_at' => $storedEvent->createdAt(),
                ]);
            }
        });
    }
}
