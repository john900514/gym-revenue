<?php

declare(strict_types=1);

namespace App\Actions\Clients\Activity\Comms;

use App\Aggregates\Clients\ClientAggregate;
use App\Domain\Clients\Models\ClientGatewayIntegration;
use App\Domain\Users\Models\User;
use App\Models\Clients\Features\CommAudience;
use App\Models\Clients\Features\SmsCampaigns;
use App\Models\Endusers\AudienceMember;
use App\Models\GatewayProviders\GatewayProvider;
use App\Models\Utility\AppState;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class FireOffSmsCampaign
{
    use AsAction;

    public string $commandSignature   = 'sms-campaigns:fire {sms_campaign_id}';
    public string $commandDescription = 'Fires off the SMS for a given sms_campaign_id.';

    protected $tokens = ['name'];

    public function handle(string $sms_campaign_id): void
    {
        $gateway  = null;
        $campaign = SmsCampaigns::with(['schedule', 'assigned_template', 'assigned_audience'])
            ->findOrFail($sms_campaign_id);
        foreach ($campaign->assigned_template as $assigned_template) {
            $templates[] = \App\Domain\Templates\SmsTemplates\Projections\SmsTemplate::with('gateway')->findOrFail($assigned_template->value);
        }
        foreach ($templates as $template) {
            $gatewayIntegrations[] = ClientGatewayIntegration::whereNickname($template->gateway->value)->whereClientId($campaign->client_id)->firstOrFail();
            $markups[]             = $template->markup;
        }
        foreach ($gatewayIntegrations as $gatewayIntegration) {
            $gateway = GatewayProvider::findOrFail($gatewayIntegration->gateway_id);
        }
        foreach ($campaign->assigned_audience as $assigned_audience) {
            $audiences[] = CommAudience::findOrFail($assigned_audience->value);
        }
        foreach ($audiences as $audience) {
            $audience_members[] = AudienceMember::whereAudienceId($audience->id)->get();
        }

        $client_aggy = ClientAggregate::retrieve($campaign->client_id);
        foreach ($audience_members as $audience_member_breakdown) {
            foreach ($audience_member_breakdown as $audience_member) {
                $sent_to = [];
                $entity  = null;
                switch ($audience_member->entity_type) {
                    case 'user':
                        $entity = User::with('phone')->find($audience_member->entity_id);

                        break;
                    default:
                        //todo:report error - unknown entity_Type
                        break;
                }
                if ($entity) {
                    if ($entity->phone) {
                        //TODO: we need to scrutinize phone format here
                        if (! AppState::isSimuationMode()) {
                            foreach ($markups as $markup) {
                                $sent_to[] = [
                                    'entity_type' => $audience_member->entity_type,
                                    'entity_id' => $audience_member->entity_id,
                                    'phone' => $entity->phone->value,
                                    'gateway' => $gateway,
                                ];
                                $client_aggy->smsSent($sms_campaign_id, $sent_to, Carbon::now(), true)->persist();
                            }
                        }
                    }
                }
            }
        }
        $client_aggy->completeSmsCampaign($sms_campaign_id, Carbon::now())->persist();
    }

    public function asCommand(Command $command): void
    {
        $this->handle(
            $command->argument('sms_campaign_id')
        );
        if (AppState::isSimuationMode()) {
            $command->info('SMS Campaign SMS skipped because app is in simulation mode');
        } else {
            $command->info('SMS Sent!');
        }
    }


    protected function transform($string, $data)
    {
        foreach ($this->tokens as $token) {
            $string = str_replace("%{$token}%", $data[$token] ?? 'UNKNOWN_TOKEN', $string);
        }

        return $string;
    }//command for ez development testing
}
