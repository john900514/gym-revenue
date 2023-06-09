<?php

declare(strict_types=1);

namespace App\Domain\Templates\CallScriptTemplates;

use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateCreated;
use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateDeleted;
use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateRestored;
use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateThumbnailUpdated;
use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateTrashed;
use App\Domain\Templates\CallScriptTemplates\Events\CallScriptTemplateUpdated;
use App\Domain\Templates\CallScriptTemplates\Projections\CallScriptTemplate;
use App\Domain\Users\Models\User;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class CallScriptTemplateProjector extends Projector
{
    public function onStartingEventReplay(): void
    {
        CallScriptTemplate::truncate();
    }

    public function onCampaignTemplateCreated(CallScriptTemplateCreated $event): void
    {
        $template_data = array_filter($event->payload, function ($key) {
            return in_array($key, (new CallScriptTemplate())->getFillable());
        }, ARRAY_FILTER_USE_KEY);

        $template_data['created_by_user_id'] = $event->modifiedBy();
        $template_data['active']             = 0;//TODO: do we really need to set template to inactive? prob only campaign

        $template            = new CallScriptTemplate();
        $template->id        = $event->aggregateRootUuid();
        $template->client_id = $event->clientId();
        $template->fill($template_data);
        $template->writeable()->save();

        $msg = 'Template was auto-generated';
        if (! $event->autoGenerated()) {
            $user = User::find($event->userId());
            $msg  = 'Template was created by ' . $user->name . ' on ' . $event->createdAt()->format('Y-m-d');
        }
    }

    public function onCampaignTemplateUpdated(CallScriptTemplateUpdated $event): void
    {
        CallScriptTemplate::findOrFail($event->aggregateRootUuid())->writeable()->updateOrFail($event->payload);
        if (! $event->autoGenerated()) {
            $user = User::find($event->modifiedBy());
            $msg  = 'Template was updated by ' . $user->name . ' on ' . $event->createdAt()->format('Y-m-d');
        }
    }

    public function onCampaignTemplateTrashed(CallScriptTemplateTrashed $event): void
    {
        CallScriptTemplate::withTrashed()->findOrFail($event->aggregateRootUuid())->writeable()->delete();
    }

    public function onCampaignTemplateRestored(CallScriptTemplateRestored $event): void
    {
        CallScriptTemplate::withTrashed()->findOrFail($event->aggregateRootUuid())->writeable()->restore();
    }

    public function onCampaignTemplateDeleted(CallScriptTemplateDeleted $event): void
    {
        CallScriptTemplate::withTrashed()->findOrFail($event->aggregateRootUuid())->writeable()->forceDelete();
    }

    public function onCampaignTemplateThumbnailUpdated(CallScriptTemplateThumbnailUpdated $event): void
    {
        CallScriptTemplate::findOrFail($event->aggregateRootUuid())->writeable()->updateOrFail(['thumbnail' => ['key' => $event->key, 'url' => $event->url]]);
    }
}
