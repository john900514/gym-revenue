<?php

namespace App\Http\Controllers;

use App\Domain\Audiences\Audience;
use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\Domain\Campaigns\ScheduledCampaigns\ScheduledCampaign;
use App\Domain\LeadTypes\LeadType;
use App\Domain\Templates\CallScriptTemplates\Projections\CallScriptTemplate;
use App\Domain\Templates\EmailTemplates\Projections\EmailTemplate;
use App\Domain\Templates\SmsTemplates\Projections\SmsTemplate;
use App\Models\Endusers\MembershipType;
use App\Support\CurrentInfoRetriever;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use ReflectionClass;

//use App\Domain\Campaigns\ScheduledCampaigns\Actions\CreateScheduledCampaign;
//use App\Domain\Campaigns\ScheduledCampaigns\Actions\UpdateScheduledCampaign;

class MassCommunicationController extends Controller
{
    public function index(string $type = 'scheduled')
    {
        $client_id = request()->user()->client_id;
        if (! $client_id) {
            return Redirect::route('dashboard');
        }

        $user = auth()->user();
        $team = CurrentInfoRetriever::getCurrentTeam();
        $campaignType = null;
        if ($type === 'scheduled') {
            $campaignType = new ScheduledCampaign();
        } elseif ($type === 'drip') {
            $campaignType = new DripCampaign();
        } else {
            abort(404, "Unknown campaign type: $campaignType");
        }
        $data = $this->getDashData($campaignType);
        $data['teamName'] = $team->name;

        if (! is_null($team->client)) {
            return Inertia::render(
                'MassCommunication/Show',
                $data
            );
        } else {
            abort(403);
        }
    }

    protected function getDashData(DripCampaign | ScheduledCampaign $model): array
    {
        $audience = Audience::all();
        $member_types = MembershipType::all()->unique('name');
        $lead_types = LeadType::all()->unique('name');

        $email_templates = EmailTemplate::with('creator')
            ->filter(request()->only('search', 'trashed'))
            ->sort()->get();

        $sms_templates = SmsTemplate::with('creator')
            ->filter(request()->only('search', 'trashed'))
            ->sort()->get();

        $call_script_templates = CallScriptTemplate::with('creator')
            ->whereUseOnce(false)
            ->filter(request()->only('search', 'trashed'))
            ->sort()->get();

        if ($model->getEntity() == DripCampaign::class) {
            $campaigns = $model::with('days')->get();
        } else {
            $campaigns = $model::all();
        }


        return
            [
                'topolApiKey' => env('TOPOL_API_KEY'),
                'plansUrl' => env('APP_URL') . "/api/plans",
                'filters' => request()->all('search', 'trashed'),
                'email_templates' => $email_templates->toArray(),
                'sms_templates' => $sms_templates->toArray(),
                'call_templates' => $call_script_templates->toArray(),
                'audiences' => $audience,
                'member_types' => $member_types,
                'lead_types' => $lead_types,
                'campaigns' => $campaigns,
                'type' => (new ReflectionClass($model))->getShortName(),
            ];
    }

    public function campaignDash(string $type = "scheduled")
    {
        $client_id = request()->user()->client_id;
        if (! $client_id) {
            return Redirect::route('dashboard');
        }

        $campaignType = null;
        if ($type === 'scheduled') {
            $campaignType = new ScheduledCampaign();
        } elseif ($type === 'drip') {
            $campaignType = new DripCampaign();
        } else {
            abort(404, "Unknown campaign type: $campaignType");
        }
        $data = $this->getDashData($campaignType);

        return Inertia::render(
            "MassCommunication/CampaignDash",
            $data
        );
    }

    public function getDripCampaign(DripCampaign $dripCampaign)
    {
        return $dripCampaign->load('days');
    }

    public function getScheduledCampaign(ScheduledCampaign $scheduledCampaign)
    {
        return $scheduledCampaign;
    }
}
