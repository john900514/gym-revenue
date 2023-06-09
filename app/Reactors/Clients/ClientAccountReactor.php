<?php

declare(strict_types=1);

namespace App\Reactors\Clients;

use App\Actions\Clients\Activity\Comms\FireOffEmailCampaign;
use App\Actions\Clients\Activity\Comms\FireOffSmsCampaign;
use App\Domain\Templates\EmailTemplates\Projections\EmailTemplate;
use App\Domain\Templates\SmsTemplates\Projections\SmsTemplate;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\User;
use App\Models\Comms\QueuedEmailCampaign;
use App\Models\Comms\QueuedSmsCampaign;
use App\Services\GatewayProviders\Email\EmailGatewayProviderService;
use App\Services\GatewayProviders\SMS\SMSGatewayProviderService;
use App\StorableEvents\Clients\Activity\Campaigns\EmailCampaignLaunched;
use App\StorableEvents\Clients\Activity\Campaigns\EmailSent;
use App\StorableEvents\Clients\Activity\Campaigns\SmsCampaignLaunched;
use App\StorableEvents\Clients\Activity\Campaigns\SmsSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class ClientAccountReactor extends Reactor implements ShouldQueue
{
    public function onEmailCampaignLaunched(EmailCampaignLaunched $event): void
    {
        if (strtotime($event->date) <= strtotime('now')) {
            //launch campaign
            FireOffEmailCampaign::dispatch($event->campaign);
        } else {
            //queue campaign
            QueuedEmailCampaign::create(['email_campaign_id' => $event->campaign, 'trigger_at' => $event->date]);
        }
    }

    public function onSmsCampaignLaunched(SmsCampaignLaunched $event): void
    {
        if (strtotime($event->date) <= strtotime('now')) {
            //launch campaign
            FireOffSmsCampaign::dispatch($event->campaign);
        } else {
            //queue campaign
            QueuedSmsCampaign::create(['sms_campaign_id' => $event->campaign, 'trigger_at' => $event->date]);
        }
    }

    public function onSmsSent(SmsSent $event): void
    {
        if ($event->isCampaign) {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy       = UserAggregate::retrieve($event->sentTo['entity_id']);
                $gateway_service = new SMSGatewayProviderService(SmsTemplate::find($event->campaign));
                $gateway_service->initSMSGateway($event->sentTo['entity_id']);
                $response = $gateway_service->fire($user_aggy->getPhoneNumber());
            } //@todo finish logic for entity_type != user::class
        } else {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy = UserAggregate::retrieve($event->sentTo['entity_id']);
            }
        }
    }

    public function onEmailSent(EmailSent $event): void
    {
        if ($event->isCampaign) {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy       = UserAggregate::retrieve($event->sentTo['entity_id']);
                $gateway_service = new EmailGatewayProviderService(EmailTemplate::find($event->campaign));
                $gateway_service->initEmailGateway($event->sentTo['entity_id']);
                $response = $gateway_service->fire($user_aggy->getEmailAddress());
            }//@todo finish logic for entity_type != user::class
        } else {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy = UserAggregate::retrieve($event->sentTo['entity_id']);
            }
        }
    }
}
