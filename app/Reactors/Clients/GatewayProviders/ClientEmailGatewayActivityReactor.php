<?php

declare(strict_types=1);

namespace App\Reactors\Clients\GatewayProviders;

use App\Domain\Templates\EmailTemplates\Projections\EmailTemplate;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Services\GatewayProviders\Email\EmailGatewayProviderService;
use App\StorableEvents\Clients\Activity\GatewayProviders\Email\UserSentATestEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class ClientEmailGatewayActivityReactor extends Reactor implements ShouldQueue
{
    public function onUserSentATestEmail(UserSentATestEmail $event): void
    {
        $user_aggy = UserAggregate::retrieve($event->user);

        // Initialize the SMS Gateway Service
        $gateway_service = new EmailGatewayProviderService(EmailTemplate::find($event->template));

        // use the service to get the correct gateway & pull the profile
        $gateway_service->initEmailGateway($event->user);

        // Get the user's email and use the service to fire the message
        $response = $gateway_service->fire($user_aggy->getEmailAddress());

        // In all cases do the User aggy to log the action
        $user_aggy->logClientEmailActivity($event->subject, $event->template, $response, $event->client)->persist();
        /**
         * STEPS
         * 7.
         * 8. In client cases, log the action with the client too, so that it's projector can add an invoicing record and activity_history
         */
    }
}
