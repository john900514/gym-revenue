<?php

namespace App\Actions\Clients\Activity\Comms;

use App\Actions\Sms\Twilio\FireTwilioMsg;
use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Features\CommAudience;
use App\Models\Clients\Features\SmsCampaigns;
use App\Models\Comms\QueuedSmsCampaign;
use App\Models\Comms\SmsTemplates;
use App\Models\Endusers\AudienceMember;
use App\Models\GatewayProviders\ClientGatewayIntegration;
use App\Models\GatewayProviders\GatewayProvider;
use App\Models\User;
use App\Models\Utility\AppState;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class FireOffSmsCampaign
{
    use AsAction;

    public string $commandSignature = 'sms-campaigns:fire {sms_campaign_id}';
    public string $commandDescription = 'Fires off the SMS for a given sms_campaign_id.';

    protected $tokens = ['name'];

    public function handle(string $sms_campaign_id)
    {
        $campaign = SmsCampaigns::with(['schedule', 'assigned_template', 'assigned_audience'])->findOrFail($sms_campaign_id);
        $template = SmsTemplates::with('gateway')->findOrFail($campaign->assigned_template->value);
        $markup = $template->markup;
        //$audience = CommAudience::findOrFail($campaign->assigned_audience->value);
        //$audience_members = AudienceMember::whereAudienceId($audience->id)->get();

        foreach ($campaign->assigned_audience as $assigned_audience)
        {
            $audiences[] = CommAudience::findOrFail($assigned_audience->value);
        }
        foreach ($audiences as $audience)
        {
            $audience_members[] = AudienceMember::whereAudienceId($audience->id)->get();
        }

        $gatewayIntegration = ClientGatewayIntegration::whereNickname($template->gateway->value)->whereClientId($campaign->client_id)->firstOrFail();
        $gateway = GatewayProvider::findOrFail($gatewayIntegration->gateway_id);
        $client_aggy = ClientAggregate::retrieve($campaign->client_id);
        foreach ($audience_members as $audience_member_breakdown)
        {
            foreach ($audience_member_breakdown as $audience_member)
            {
                $sent_to = [];
                $member = null;
                switch ($audience_member->entity_type) {
                    case 'user':
                        $member = User::with('phone')->find($audience_member->entity_id);
                        break;
                    case 'prospect':
//                        $member = Prospects::findOrFail($audience_member->entity_id);
                        break;
                    case 'conversion':
//                        $member = Conversions::findOrFail($audience_member->entity_id);
                        break;
                    default:
                        //todo:report error - unknown entity_Type
                        break;
                }
                if ($member) {
                    if ($member->phone) {
                        //TODO: we need to scrutinize phone format here
                        if (!AppState::isSimuationMode()) {
                            FireTwilioMsg::dispatch($member->phone->value, $this->transform($markup, $member->toArray()));
                        }
                    }
                    $sent_to[] = ['entity_type' => $audience_member->entity_type, 'entity_id' => $audience_member->entity_id, 'phone' => $member->phone->value];
                }
                $client_aggy->logSmsSent($sms_campaign_id, $sent_to, Carbon::now())->persist();
            }
        }
        $client_aggy->completeSmsCampaign($sms_campaign_id, Carbon::now())->persist();
    }

    protected function transform($string, $data)
    {
        foreach ($this->tokens as $token) {
            $string = str_replace("%{$token}%", $data[$token] ?? 'UNKNOWN_TOKEN', $string);
        }
        return $string;
    }

    //command for ez development testing
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
}
