<?php

declare(strict_types=1);

namespace App\Domain\Roles;

use App\Domain\CalendarEvents\CalendarEvent;
use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\Domain\Campaigns\ScheduledCampaigns\ScheduledCampaign;
use App\Domain\Chat\Models\Chat;
use App\Domain\Clients\Projections\Client;
use App\Domain\Conversations\Twilio\Models\ClientConversation;
use App\Domain\Departments\Department;
use App\Domain\EntrySourceCategories\EntrySourceCategory;
use App\Domain\EntrySources\EntrySource;
use App\Domain\LeadStatuses\LeadStatus;
use App\Domain\Locations\Projections\Location;
use App\Domain\Reminders\Reminder;
use App\Domain\Teams\Models\Team;
use App\Domain\Templates\CallScriptTemplates\Projections\CallScriptTemplate;
use App\Domain\Templates\EmailTemplates\Projections\EmailTemplate;
use App\Domain\Templates\SmsTemplates\Projections\SmsTemplate;
use App\Domain\Users\Models\Customer;
use App\Domain\Users\Models\EndUser;
use App\Domain\Users\Models\Lead;
use App\Domain\Users\Models\Member;
use App\Domain\Users\Models\User;
use App\Enums\SecurityGroupEnum;
use App\Models\DynamicReport;
use App\Models\File;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Position;
use App\Models\Traits\Sortable;
use Bouncer;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Silber\Bouncer\Database\Concerns\HasAbilities;
use Twilio\TwiML\Voice\Task;

/**
 * @method static RoleFactory factory()
 */
class Role extends \Silber\Bouncer\Database\Role
{
    use HasFactory;
    use HasAbilities;
    use Sortable;

    /** @var array<string> */
    protected $fillable = ['name', 'title', 'group'];

    /**
     * @param array<string, mixed> $filters
     *
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search): void {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%');
                $query->where('title', 'like', '%' . $search . '%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed): void {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function abilities()
    {
        if (Bouncer::role()->find($this->id)) {
            return Bouncer::role()->find($this->id)->getAbilities()->toArray();
        } else {
            return null;
        }
    }

    public function availableAbilities()
    {
        return Bouncer::ability()->whereEntityId(null)->get(['name', 'title', 'id']);
    }

    public function securityGroups()
    {
        return collect(SecurityGroupEnum::cases())->keyBy('name')->except('ADMIN')->values()->map(function ($s) {
            return ['value' => $s->value, 'name' => $s->name];
        });
    }

    public static function getEntityFromGroup(string $group): ?string
    {
        return match ($group) {
            'users' => User::class,
            'leads' => Lead::class,
            'customers' => Customer::class,
            'members' => Member::class,
            'locations' => Location::class,
            'endusers' => EndUser::class,
            'lead-statuses' => LeadStatus::class,
            'lead-sources' => EntrySource::class,
            'teams' => Team::class,
            'files' => File::class,
            'calendar' => CalendarEvent::class,
            'roles' => Role::class,
            'task' => Task::class,
            'positions' => Position::class,
            'departments' => Department::class,
            'reminders' => Reminder::class,
            'client' => Client::class,
            'email-templates' => EmailTemplate::class,
            'sms-templates' => SmsTemplate::class,
            'notes' => Note::class,
            'folders' => Folder::class,
            'drip-campaigns' => DripCampaign::class,
            'scheduled-campaigns' => ScheduledCampaign::class,
            'call-templates' => CallScriptTemplate::class,
            'dynamic-reports' => DynamicReport::class,
            'chat' => Chat::class,
            'conversation' => ClientConversation::class,
            'customers' => Customer::class,
            'entry-source-category' => EntrySourceCategory::class,
            default => null,
        };
    }

    /**
     * Create a new factory instance for the model.
     *
     */
    protected static function newFactory(): Factory
    {
        return RoleFactory::new();
    }
}
