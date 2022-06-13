<?php

namespace App\Reactors\Clients;

use App\Actions\Clients\Activity\Comms\FireOffEmailCampaign;
use App\Actions\Clients\Activity\Comms\FireOffSmsCampaign;
use App\Aggregates\Clients\ClientAggregate;
use App\Aggregates\Users\UserAggregate;
use App\Domain\Clients\Actions\UpdateClient;
use App\Domain\Clients\Events\ClientCreated;
use App\Domain\Clients\Models\Client;
use App\Domain\Teams\Actions\CreateTeam;
use App\Domain\Teams\Models\Team;
use App\Models\Comms\EmailTemplates;
use App\Models\Comms\QueuedEmailCampaign;
use App\Models\Comms\QueuedSmsCampaign;
use App\Models\Comms\SmsTemplates;
use App\Models\User;
use App\Services\GatewayProviders\Email\EmailGatewayProviderService;
use App\Services\GatewayProviders\SMS\SMSGatewayProviderService;
use App\StorableEvents\Clients\Activity\Campaigns\EmailCampaignLaunched;
use App\StorableEvents\Clients\Activity\Campaigns\EmailSent;
use App\StorableEvents\Clients\Activity\Campaigns\SmsCampaignLaunched;
use App\StorableEvents\Clients\Activity\Campaigns\SmsSent;
use App\StorableEvents\Clients\TeamAttachedToClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class ClientAccountReactor extends Reactor implements ShouldQueue
{
    public function onClientCreated(ClientCreated $event)
    {
        $client = Client::findOrFail($event->aggregateRootUuid());
        $default_team_name = $client->name . ' Home Office';
        $home_team = CreateTeam::run(['name' => $default_team_name, 'client_id' => $event->aggregateRootUuid(), 'home_team' => true]);
        UpdateClient::run($client->id, ['home_team_id' => $home_team->id]);
    }
//    public function onTeamAttachedToClient(TeamAttachedToClient $event)
//    {
//        $team = Team::findOrFail($event->team);
//
//        if ($team->home_team) {
//            ClientAggregate::retrieve($event->aggregateRootUuid())
//                ->addCapeAndBayAdminsToTeam($event->team)
//                ->persist();
//        }
//    }

    public function onEmailCampaignLaunched(EmailCampaignLaunched $event)
    {
        if (strtotime($event->date) <= strtotime('now')) {
            //launch campaign
            FireOffEmailCampaign::dispatch($event->campaign);
        } else {
            //queue campaign
            QueuedEmailCampaign::create(['email_campaign_id' => $event->campaign, 'trigger_at' => $event->date]);
        }
    }

    public function onSmsCampaignLaunched(SmsCampaignLaunched $event)
    {
        if (strtotime($event->date) <= strtotime('now')) {
            //launch campaign
            FireOffSmsCampaign::dispatch($event->campaign);
        } else {
            //queue campaign
            QueuedSmsCampaign::create(['sms_campaign_id' => $event->campaign, 'trigger_at' => $event->date]);
        }
    }

    public function onSmsSent(SmsSent $event)
    {
        if ($event->isCampaign) {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy = UserAggregate::retrieve($event->sentTo['entity_id']);
                $gateway_service = new SMSGatewayProviderService(SmsTemplates::find($event->campaign));
                $gateway_service->initSMSGateway($event->sentTo['entity_id']);
                $response = $gateway_service->fire($user_aggy->getPhoneNumber());
            } //@todo finish logic for entity_type != user::class
        } else {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy = UserAggregate::retrieve($event->sentTo['entity_id']);
            }
        }
    }

    public function onEmailSent(EmailSent $event)
    {
        if ($event->isCampaign) {
            if ($event->sentTo['entity_type'] == User::class) {
                $user_aggy = UserAggregate::retrieve($event->sentTo['entity_id']);
                $gateway_service = new EmailGatewayProviderService(EmailTemplates::find($event->campaign));
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
