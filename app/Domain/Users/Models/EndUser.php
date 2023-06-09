<?php

declare(strict_types=1);

namespace App\Domain\Users\Models;

use App\Domain\Locations\Projections\Location;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Models\Endusers\MembershipType;
use App\Scopes\ClientScope;
use App\Scopes\EndUserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EndUser extends User
{
    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'gymrevenue_id', 'home_location_id');
    }

    public function getMembershipTypeIdAttribute(): ?string
    {
        return $this->details['membership_type_id'] ?? null;
    }

    public function membershipType(): HasOne
    {
        return $this->hasOne(MembershipType::class, 'id', $this->membership_type_id);
    }

//    TODO: needs to be reworked to use actual relationships post vlookup detail removal
    public function claimed(): mixed
    {
        return $this->details['claimed'] ?? null;
    }

    //TODO: should utilize a relationship
    public function getOwnerUserIdAttribute(): ?string
    {
        return $this->details['owner_user_id'] ?? null;
    }

    //TODO: should utilize a relationship
    public function getOwnerAttribute(): ?Employee
    {
        return Employee::find($this->owner_user_id);
    }

    /**
     * @param array<string, mixed> $filters
     *
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search): void {
            $searchable_phrase = "%{$search}%";
            $query->where(function ($query) use ($searchable_phrase): void {
                $query->where('phone', 'like', $searchable_phrase)
                    ->orWhere('email', 'like', $searchable_phrase)
                    ->orWhere('first_name', 'like', $searchable_phrase)
                    ->orWhere('last_name', 'like', $searchable_phrase)
                    ->orWhere('home_location_id', 'like', $searchable_phrase)
                    ->orWhere('agreement_id', 'like', $searchable_phrase)
                    ->orWhereHas('location', function ($query) use ($searchable_phrase): void {
                        $query->where('name', 'like', $searchable_phrase);
                    })
                    ->orWhereHas('client', function ($query) use ($searchable_phrase): void {
                        $query->where('name', 'like', $searchable_phrase);
                    });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed): void {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
            /* created date will need a calendar date picker and the endusers need different created_at dates */
        })->when($filters['createdat'] ?? null, function ($query, $createdat): void {
            $query->where('created_at', 'like', $createdat . '%');

            /* filters for typeof lenduser the data schema changed so lets get back to this */
        })->when($filters['grlocation'] ?? null, function ($query, $grlocation): void {
            $query->whereIn('home_location_id', $grlocation);

            /* Filter for EndUser Sources */
        })->when($filters['opportunity'] ?? null, function ($query, $opportunity): void {
            $query->whereIn('opportunity', $opportunity);
        })->when($filters['claimed'] ?? null, function ($query, $owner_user_id): void {
            $query->whereOwnerUserId($owner_user_id);
        })->when($filters['date_of_birth'] ?? null, function ($query, $dob): void {
            $query->whereBetween('date_of_birth', $dob);
        })->when($filters['lastupdated'] ?? null, function ($query, $search): void {
            $query->orderBy('updated_at', $search);
            /** Everything below already is redundant bc of the main search - but if it's in the ticket we do it. */
        })->when($filters['nameSearch'] ?? null, function ($query, $search): void {
            $searchable_phrase = "%{$search}%";
            $query->where('first_name', 'like', $searchable_phrase)
                ->orWhere('last_name', 'like', $searchable_phrase);
        })->when($filters['phoneSearch'] ?? null, function ($query, $search): void {
            $query->where('phone', 'like', '%' . $search . '%');
        })->when($filters['emailSearch'] ?? null, function ($query, $search): void {
            $query->where('email', 'like', '%' . $search . '%');
        })->when($filters['agreementSearch'] ?? null, function ($query, $search): void {
            $query->orWhere('agreement_id', 'like', '%' . $search . '%');
        });
    }

    public function isCBorGR(): bool
    {
        return (str_ends_with($this->email, '@capeandbay.com') || str_ends_with($this->email, '@gymrevenue.com'));
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany('App\Models\File', 'fileable');
    }

    //    TODO: store as projection somewhere, this is expensive
    public function getInteractionCount()
    {
        $aggy = UserAggregate::retrieve($this->id);

        return $aggy->getInteractionCount();
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new ClientScope());
        static::addGlobalScope(new EndUserScope());
    }
}
