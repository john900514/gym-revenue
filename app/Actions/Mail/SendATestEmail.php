<?php

namespace App\Actions\Email;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\ClientDetail;
use App\Models\Comms\EmailTemplates;
use App\Services\GatewayProfiles\Email\EmailGatewayProviderService;
use Lorisleiva\Actions\Concerns\AsAction;

class SendATestEmail
{
    use AsAction {
        __invoke as protected invokeFromLaravelActions;
    }

    public function __invoke()
    {
        // ...
    }

    public function rules() : array
    {
        return [
            'templateId' => 'bail|required|exists:email_templates,id'
        ];
    }

    public function handle()
    {
        $results = false;

        $data = request()->all();
        $user = request()->user();
        $email_detail = $user->email;

        // Get the user and check if there is an email or fail with string
        if(!is_null($email_detail))
        {
            // Get the sms template from sms templates or fail with string
            $email_template_record = EmailTemplates::find($data['templateId']);

            if(!is_null($email_template_record))
            {
                $current_team_id = $user->current_team_id;

                $client_id = null;
                if(!is_null($current_team_id))
                {
                    $client_team_detail = ClientDetail::whereDetail('team')
                        ->whereValue($current_team_id)->first();
                    $client_id = $client_team_detail->client_id ?? null;
                }

                // Verify the sms going with the client of the active team is the same or its a gymrevenue template
                if($client_id == $email_template_record->client_id)
                {

                    if(is_null($client_id))
                    {
                        /**
                         * STEPS
                         * @todo - make a set of gateway provider services
                         * 4. If the active team's client is gymrevenue, use the service, to easy fire the message with the built in integration with Twilio
                         * 5. else use the service to get the correct gateway
                         * 6. use the service to pull the profile of the gateway and fire
                         * 7. In all cases do the User aggy to log the action
                         * 8. In client cases, log the action with the client too, so that it's projector can add an invoicing record and activity_history
                         */
                        /**
                         * Actual Steps
                         * @todo - make an AdminUserGatewayActivityAggregate and attach it to UserAggy with Bouncer ACL
                         * 1. Send the test message event
                         */
                        $results = 'Cape & Bay Teams Are Not Ready Yet.';
                    }
                    else
                    {
                        ClientAggregate::retrieve($client_id)->getGatewayAggregate()
                            ->sendATestSMSMessage($email_template_record->id, $user->id)
                            ->persist();
                        $results = true;

                    }

                }
                else
                {
                    $results = 'This template does not belong to this Account.';
                }
            }
            else
            {
                $results = 'Invalid or decommissioned template.';
            }
        }
        else
        {
            $results = 'Missing Email on Profile. Add it to use this feature.';
        }

        return $results;
    }

    public function jsonResponse($result)
    {
        $results = ['success' => false, 'message' => 'Server Error. Message not sent'];
        $code = 500;

        if($result)
        {
            if(is_string($result))
            {
                $results['message'] = $result;
                $code = 401;
            }
            else
            {
                $results['success'] = true;
                $results['message'] = 'Success';
                $code = 200;
            }
        }

        return response($results, $code);
    }
}
