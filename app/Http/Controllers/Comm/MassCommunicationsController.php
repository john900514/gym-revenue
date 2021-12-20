<?php

namespace App\Http\Controllers\Comm;

use App\Aggregates\Clients\ClientAggregate;
use App\Http\Controllers\Controller;
use App\Models\Clients\Client;
use App\Models\Clients\Features\CommAudience;
use App\Models\Clients\Features\EmailCampaigns;
use App\Models\Clients\Features\SmsCampaigns;
use App\Models\Comms\EmailTemplateDetails;
use App\Models\Comms\EmailTemplates;
use App\Models\Comms\SmsTemplates;
use App\Models\Endusers\Lead;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Prologue\Alerts\Facades\Alert;
use function Aws\filter;

class MassCommunicationsController extends Controller
{
    private function getStats(string $client_id = null)
    {
        $results = [
            'email_templates' => [
                'active' => 0,
                'created' => 0
            ],
            'sms_templates' => [
                'active' => 0,
                'created' => 0
            ],
            'email_campaigns' => [
                'active' => 0,
                'created' => 0
            ],
            'sms_campaigns' => [
                'active' => 0,
                'created' => 0
            ],
            'total_audience' => 0,
            'audience_breakdown' => [
                'all' => 0
            ]
        ];

        if (!is_null($client_id)) {
            $results['total_audience'] = Lead::whereClientId($client_id)->count();
            $results['audience_breakdown'] = [
                'all' => Lead::whereClientId($client_id)->count(),
                'prospects' => Lead::whereClientId($client_id)->count(),
                'conversions' => 0
            ];
            $results['sms_templates'] = [
                'created' => SmsTemplates::whereClientId($client_id)->count(),
                'active' => SmsTemplates::whereClientId($client_id)->whereActive(1)->count(),
            ];
            $results['sms_campaigns'] = [
                'created' => SmsCampaigns::whereClientId($client_id)->count(),
                'active' => SmsCampaigns::whereClientId($client_id)->whereActive(1)->count(),
            ];
            $results['email_templates'] = [
                'created' => EmailTemplates::whereClientId($client_id)->count(),
                'active' => EmailTemplates::whereClientId($client_id)->whereActive(1)->count(),
            ];
            $results['email_campaigns'] = [
                'created' => EmailCampaigns::whereClientId($client_id)->count(),
                'active' => EmailCampaigns::whereClientId($client_id)->whereActive(1)->count(),
            ];
        } else {
            $results['total_audience'] = 25;
            $results['audience_breakdown'] = [
                'all' => 25,
                'admins' => 10,
                'employees' => 15
            ];
        }

        return $results;
    }

    private function setupCampaignsObject(bool $is_client_user, string $type, string $client_id = null)
    {
        $results = [];

        if ((!is_null($client_id))) {
            // Get the current client or its cape and bay
            $current_team = request()->user()->currentTeam()->first();
            $client = Client::whereId($client_id)->with('default_team_name')->first();

            // Get the correct Model
            $template_model = ($type == 'email') ? new EmailCampaigns() : new SmsCampaigns();
            // Query for all templates with that client id
            $template_model = $template_model->whereClientId($client_id);

            /**
             * STEPS
             * 2.
             * @todo - also add team_id if team_id or null if default team
             * @todo - if the team has scoped clubs, get the query's details for clubs and filter
             *
             */
            $results = $template_model;
        } else {
            if (!$is_client_user) {
                $template_model = ($type == 'email') ? new EmailCampaigns() : new SmsCampaigns();
                $template_model = $template_model->whereNull('client_id');
                /**
                 * STEPS
                 * 2.
                 * @todo - also add team_id if team_id or null if default team
                 * @todo - if the team has scoped clubs, get the query's details for clubs and filter
                 *
                 */
                $results = $template_model;
            }
        }

        return $results;
    }

    private function setupTemplatesObject(bool $is_client_user, string $type, string $client_id = null)
    {
        $results = [];

        if ((!is_null($client_id))) {
            // Get the current client or its cape and bay
            $current_team = request()->user()->currentTeam()->first();
            $client = Client::whereId($client_id)->with('default_team_name')->first();

            // Get the correct Model
            $template_model = ($type == 'email') ? new EmailTemplates() : new SmsTemplates();
            // Query for all templates with that client id
            $template_model = $template_model->whereClientId($client_id);

            /**
             * STEPS
             * 2.
             * @todo - also add team_id if team_id or null if default team
             * @todo - if the team has scoped clubs, get the query's details for clubs and filter
             *
             */
            $results = $template_model;
        } else {
            if (!$is_client_user) {
                $template_model = ($type == 'email') ? new EmailTemplates() : new SmsTemplates();
                $template_model = $template_model->whereNull('client_id');
                /**
                 * STEPS
                 * 2.
                 * @todo - also add team_id if team_id or null if default team
                 * @todo - if the team has scoped clubs, get the query's details for clubs and filter
                 *
                 */
                $results = $template_model;
            }
        }


        return $results;
    }

    private function filterHistoryLog(array $history_log, string $audience, string $client_id = null)
    {
        $results = [];
//dd($audience, $history_log);
        // look for logs about the audience
        foreach ($history_log as $idx => $log)
        {

            if(array_key_exists('slug', $log) && ($log['slug'] == $audience))
            {
                $results[] = $log;
            }
        }
        //dd($results);

        return $results;
    }

    public function index(Request $request)
    {
        $client_id = request()->user()->currentClientId();
        $is_client_user = request()->user()->isClientUser();

        if (!is_null($client_id)) {
            $aggy = ClientAggregate::retrieve($client_id);
            $history_log = $aggy->getCommunicationHistoryLog();
            $aud_options = [
                'all' => 'All',
                'prospects' => 'Prospects',
                'conversions' => 'Conversions',
            ];

            // @todo - make a function that crunches these datas
            $stats = $this->getStats($client_id);
        }
        else {
            $history_log = [];
            $aud_options = [
                'all' => 'All',
                'admins' => 'Cape & Bay Admins',
                'employees' => 'Cape & Bay Non-Admins'
            ];

            $stats = $this->getStats($client_id);
        }

        $active_audience = 'all';
        if (request()->has('audience')) {
            $aud = (request()->get('audience'));
            switch ($aud) {
                case 'all':
                    //@todo - what ever is needed to filfill this need
                    $active_audience = $aud;
                    break;
                case 'prospects':
                case 'conversions':
                case 'admins':
                case 'employees':
                    //@todo - what ever is needed to filfill this need
                    $active_audience = $aud;
                    $history_log = $this->filterHistoryLog($history_log, $aud, $client_id);
                    break;

                default:
                    // @todo - this will be a uuid to be looked up
            }
        }

        //search filter
        $search = $request->query('search');
        $history_log = collect($history_log)->filter(function($value, $key) use($search){
            if(str_contains(strtolower($value['type']), strtolower($search))){
                return true;
            }else if(str_contains(strtolower($value['recordName']), strtolower($search))){
                return true;
            }else if(str_contains(strtolower($value['date']), strtolower($search))){
                return true;
            }
            else if(str_contains(strtolower($value['by']), strtolower($search))){
                return true;
            }
            return false;
        });

        return Inertia::render('Comms/MassCommsDashboard', [
            'title' => 'Mass Communications',
            'audiences' => $aud_options,
            'activeAudience' => $active_audience,
            'stats' => $stats,
            'historyFeed' => paginate_array($request, $history_log)
//            'historyFeed' => $history_log
        ]);
    }

    public function et_index()
    {
        $client_id = request()->user()->currentClientId();
        $is_client_user = request()->user()->isClientUser();

        $page_count = 10;
        $templates = [
            'data' => []
        ];

        $templates_model = $this->setupTemplatesObject($is_client_user, 'email', $client_id);

        if (!empty($templates_model)) {
            $templates = $templates_model//->with('location')->with('detailsDesc')
                    ->with('creator')
                    ->filter(request()->only('search', 'trashed'))
                    ->paginate($page_count);
        }

        return Inertia::render('Comms/Emails/Templates/EmailTemplatesIndex', [
            'title' => 'Email Templates',
            'filters' => request()->all('search', 'trashed'),
            'templates' => $templates,
        ]);
    }

    public function et_create()
    {
        return Inertia::render('Comms/Emails/Templates/CreateEmailTemplate', []);
    }

    public function et_edit($id)
    {

        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }

        $template = EmailTemplates::find($id);
        // @todo - need to build access validation here.

        return Inertia::render('Comms/Emails/Templates/EditEmailTemplate', [
            'template' => $template
        ]);
    }

    public function et_store(Request $request)
    {
        $template = $request->validate([
                'name' => 'required',
                'markup' => 'required'
            ]
        );

        $client_id = request()->user()->currentClientId();
        // @todo - this could come in handy
        //$is_client_user = request()->user()->isClientUser();
        try {
            $template['active'] = '0';
            $template['client_id'] = $client_id;
            $template['created_by_user_id'] = $request->user()->id;
            $new_template = EmailTemplates::create($template);
            Alert::info("New Template {$template['name']} was created")->flash();
        }
        catch(\Exception $e)
        {
            Alert::error("New Template {$template['name']} could not be created")->flash();
            return redirect()->back();
        }

        return Redirect::route('comms.email-templates');
//        return Redirect::route('comms.email-templates.edit', ['id' => $new_template->id]);

    }

    public function et_update(Request $request)
    {
        $data = $request->validate([
                'id' => 'required|exists:email_templates,id',
                'name' => 'required',
                'markup' => 'required',
                'active' => 'sometimes',
                'client_id' => 'required|exists:clients,id',
                'created_by_user_id' => 'required',
            ]
        );

        $template = EmailTemplates::find($data['id']);
        $old_values = $template->toArray();
        $template->name = ($template->name == $data['name']) ? $template->name : $data['name'];
        $template->markup = ($template->markup == $data['markup']) ? $template->markup : $data['markup'];
        $template->active = ($template->active == $data['active']) ? $template->active : $data['active'];
        $template->save();

        ClientAggregate::retrieve($data['client_id'])
            ->updateEmailTemplate($template->id, request()->user()->id, $old_values, $template->toArray())
            ->persist();
        /*

        */
//        $template['created_by_user_id'] = $request->user()->id;
//        SmsTemplates::create($template);
        return Redirect::route('comms.email-templates');
    }

    public function et_trash($id)
    {
        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }

        $template = EmailTemplates::findOrFail($id);
        $success = $template->deleteOrFail();
        Alert::success("Template '{$template->name}' trashed")->flash();


        return Redirect::back();
    }

    public function et_restore(Request $request, $id)
    {
        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }
        $template = EmailTemplates::withTrashed()->findOrFail($id);
        $template->restore();

        Alert::success("Template '{$template->name}' restored")->flash();

        return Redirect::back();
    }

    public function ec_index()
    {
        $client_id = request()->user()->currentClientId();
        $is_client_user = request()->user()->isClientUser();

        $page_count = 10;
        $campaigns = [
            'data' => []
        ];

        $campaigns_model = $this->setupCampaignsObject($is_client_user, 'email', $client_id);

        if (!empty($campaigns_model)) {
            $campaigns = $campaigns_model//->with('location')->with('detailsDesc')
            ->with('creator')
            ->filter(request()->only('search', 'trashed'))
                ->paginate($page_count);
        }

        return Inertia::render('Comms/Emails/Campaigns/EmailCampaignsIndex', [
            'title' => 'Email Campaigns',
            'filters' => request()->all('search', 'trashed'),
            'campaigns' => $campaigns
        ]);
    }

    public function ec_create()
    {
        return Inertia::render('Comms/Emails/Campaigns/CreateEmailCampaign', [
        ]);
    }

    public function ec_edit($id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return redirect()->back();
        }

        $available_templates = [];
        $available_audiences = [];
        $campaign = EmailCampaigns::whereId($id)
            ->with('assigned_template')->with('assigned_audience')
            ->with('schedule')->with('schedule_date')
            ->first();

        $templates = EmailTemplates::whereClientId($campaign->client_id)->whereActive('1')->get();
        foreach ($templates as $template)
        {
            $available_templates[$template->id] = $template->toArray();
        }
        $audiences = CommAudience::whereClientId($campaign->client_id)->whereActive('1')->get();
        foreach ($audiences as $audience)
        {
            $available_audiences[$audience->id] = $audience->toArray();
        }
        // @todo - need to build access validation here.
//        dd($campaign->schedule_date->toArray());
        return Inertia::render('Comms/Emails/Campaigns/EditEmailCampaign', [
            'campaign' => $campaign,
            'templates' => $available_templates,
            'audiences' => $available_audiences,
            'assigned-template' => (!is_null($campaign->assigned_template)) ? $campaign->assigned_template->value : '',
            'assigned-audience' => (!is_null($campaign->assigned_audience)) ? $campaign->assigned_audience->value : ''
        ]);
    }

    public function ec_store()
    {
        $template = request()->validate([
                'name' => 'required',
            ]
        );
        $client_id = request()->user()->currentClientId();

        try {
            $template['active'] = '0';
            $template['client_id'] = $client_id;
            $template['created_by_user_id'] = request()->user()->id;
            $new_campaign = EmailCampaigns::create($template);
            Alert::info("New Campaigns {$template['name']} was created")->flash();
        }
        catch(\Exception $e)
        {
            Alert::error("New Template {$template['name']} could not be created")->flash();
            return redirect()->back();
        }

        return Redirect::route('comms.email-campaigns.edit', ['id' => $new_campaign->id]);
    }

    public function ec_update($id)
    {
        $data = request()->validate([
                'id' => 'required|exists:email_campaigns,id',
                'name' => 'required',
                'active' => 'sometimes|bool',
                'schedule_date' => 'sometimes',
                'schedule' => 'sometimes',
                'email_template_id' => 'sometimes',
                'audience_id' => 'sometimes',
                'client_id' => 'required|exists:clients,id',
                'created_by_user_id' => 'required',
            ]
        );

        $client_aggy = ClientAggregate::retrieve($data['client_id']);
        $campaign = EmailCampaigns::whereId($data['id'])
            ->with('assigned_template')->with('assigned_audience')
            ->with('schedule')->with('schedule_date')
            ->first();
        $old_values = $campaign->toArray();

        // just to start if name is different, change and use aggy to update log
        if($old_values['name'] != $data['name'])
        {
            $campaign->name = $data['name'];
            $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'name', $data['name'], $old_values['name']);
            // @todo - log this in user_details in projector
            $campaign->save();
        }

        if($data['active'] && ($old_values['schedule_date'] === null || strtotime($old_values['schedule_date']['value']) < strtotime('now')))
        {
            // logic to activate the campaign, or update stuff if it is already so
//            collect($data)->each(function($item, $key) use($old_values, $data, $client_aggy, $campaign){
//                $old_value = '';
//                if(array_key_exists($key, $old_values)){
//                    $old_value = $old_values[$key];
//                }
//                if(is_array($old_value) && array_key_exists( 'value', $old_value)){
//                    $old_value=$old_value['value'];
//                }
//                if($old_value===1){
//                    $old_value=true;
//                }
//                if(!empty($old_value) && $item !== $old_value){
//                    dd(['key'=>$key,'old'=>$old_value, 'new' => $data[$key]]);
//                    $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,$key,  $data[$key], $old_value);
//
//                }
//            });
            $client_aggy->persist();
            if(is_null($old_values['schedule']) || ($old_values['schedule']['value'] != $data['schedule']))
            {
//                $campaign->schedule = $data['schedule'];
                $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'schedule',  $data['schedule'], $old_values['schedule']->value ?? '');
                // @todo - log this in user_details in projector
                $campaign->save();
            }
            else
            {

            }


            if($data['schedule_date'] == 'now')
            {
                $fire_date = date('Y-m-d H:i:s');
            }
            else
            {
                $fire_date = date('Y-m-d H:i:s', strtotime('now +'.$data['schedule_date']));
            }
            if( (is_null($old_values['schedule_date']) ? '' :  $old_values['schedule_date']['value']) != $data['schedule_date'])
            {
                $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'schedule_date',  $fire_date, $old_values['schedule_date']['value'] ?? '');
                // @todo - log this in user_details in projector
//                $campaign->schedule = $fire_date;
//                $campaign->save();
            }

            if(!is_null($campaign->assigned_audience))
            {
                if($campaign->assigned_audience->value != $data['audience_id'])
                {
                    $campaign->assigned_audience->active = 0;
                    $campaign->assigned_audience->save();

                    $client_aggy = $client_aggy->unassignAudienceFromEmailCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id)
                        ->assignAudienceToEmailCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                }

            }
            else
            {
                $client_aggy = $client_aggy->assignAudienceToEmailCampaign($data['audience_id'], $campaign->id, request()->user()->id);
            }

            if(!is_null($campaign->assigned_template))
            {
                if($campaign->assigned_template->value != $data['email_template_id'])
                {
                    $campaign->assigned_template->active = 0;
                    $campaign->assigned_template->save();

                    $client_aggy = $client_aggy->unassignEmailTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id)
                        ->assignEmailTemplateToCampaign($data['email_template_id'], $campaign->id, request()->user()->id);
                }

            }
            else
            {
                if($old_values['schedule_date'] !== null &&  strtotime($old_values['schedule_date']['value']) < strtotime('now')){
                    Alert::error("Campaign {$old_values['name']} was not updated. Schedule Date is in the past")->flash();
                }
                $client_aggy = $client_aggy->assignEmailTemplateToCampaign($data['email_template_id'], $campaign->id, request()->user()->id);
            }

            // active = 1 save() with aggy launchCampaign event
            $campaign->active = 1;
            $campaign->save();
            try {
                $client_aggy = $client_aggy->launchEmailCampaign($campaign->id, $fire_date, request()->user());
                $client_aggy->persist();
                Alert::success("Campaign {$campaign->name} has been updated")->flash();
                Alert::success("Campaign {$campaign->name} is now active!")->flash();
            }
            catch(\Exception $e)
            {
                // @todo - send an Alert flash indicting the fail.
                // @todo - revert all the changes made
                Alert::error("Campaign {$old_values['name']} encountered")->flash();
                Alert::error("Campaign {$old_values['name']} was not updated")->flash();
                Alert::warning("Campaign {$old_values['name']} data was reverted and not active.")->flash();
            }


            return Redirect::route('comms.email-campaigns');
        }
        else
        {
            // unset schedule and schedule_date if set and use aggy to update logs
            if(!is_null($campaign->schedule))
            {
//                $campaign->schedule = null;
                $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'schedule', $data['schedule'], $old_values['schedule']['value'] ?? '');
                // @todo - log this in user_details in projector
//                $campaign->save();
            }

            // unset schedule_date if set and use aggy to update logs or skip
            if(!is_null($campaign->schedule_date))
            {
//                $campaign->schedule_date = null;
                $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'schedule_date',  $data['schedule_date'], $old_values['schedule_date']['value']  ?? '');
                // @todo - log this in user_details in projector
//                $campaign->save();
            }

            // @todo - if audience_id is not null, set it in campaign_details and use aggy to update logs in Client and Audiences and User
            if(array_key_exists('audience_id', $data))
            {
                if(!is_null($data['audience_id']))
                {
                    if($old_values['active'] == 1) {
                        // this means its getting shut off so
                        if(!is_null($campaign->assigned_audience))
                        {
                            $campaign->assigned_audience->active = 0;
                            $campaign->assigned_audience->save();
                            $client_aggy = $client_aggy->unassignAudienceFromEmailCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                        }
                    }
                    else
                    {
                        // it was never active so it's okay to assign a template if it wasn't already
                        if(!is_null($campaign->assigned_audience))
                        {
                            $campaign->assigned_audience->active = 0;
                            $campaign->assigned_audience->save();

                            if(($campaign->assigned_audience->value != $data['audience_id']))
                            {
                                $client_aggy = $client_aggy->unassignAudienceFromEmailCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);

                                if(!is_null($data['audience_id']))
                                {
                                    $client_aggy = $client_aggy->assignAudienceToEmailCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                                }

                            }
                            else
                            {
                                $client_aggy = $client_aggy->unassignAudienceFromEmailCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                            }
                        }
                        else
                        {
                            $client_aggy = $client_aggy->assignAudienceToEmailCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                        }
                    }
                }
                else
                {
                    if(!is_null($campaign->assigned_audience))
                    {
                        $campaign->assigned_audience->active = 0;
                        $campaign->assigned_audience->save();
                        $client_aggy->unassignAudienceFromEmailCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                    }
                }
            }

            // @todo - if email_template_id is not null, set it in campaign_details and use aggy to update logs in Client and Templates and User
            if(array_key_exists('email_template_id', $data))
            {
                if((!is_null($data['email_template_id'])))
                {
                    if($old_values['active'] == 1) {
                        // this means its getting shut off so
                        if(!is_null($campaign->assigned_template))
                        {
                            $campaign->assigned_template->active = 0;
                            $campaign->assigned_template->save();
                            $client_aggy = $client_aggy->unassignEmailTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                        }
                    }
                    else {
                        // it was never active so it's okay to assign a template if it wasn't already
                        if(!is_null($campaign->assigned_template))
                        {
                            $campaign->assigned_template->active = 0;
                            $campaign->assigned_template->save();

                            if(($campaign->assigned_template->value != $data['email_template_id']))
                            {
                                $client_aggy = $client_aggy->unassignEmailTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);

                                if(!is_null($data['email_template_id']))
                                {
                                    $client_aggy = $client_aggy->assignEmailTemplateToCampaign($data['email_template_id'], $campaign->id, request()->user()->id);
                                }
                            }
                            else
                            {
                                $client_aggy = $client_aggy->unassignEmailTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                            }
                        }
                        else
                        {
                            $client_aggy = $client_aggy->assignEmailTemplateToCampaign($data['email_template_id'], $campaign->id, request()->user()->id);
                        }

                    }
                }
                else
                {
                    if(!is_null($campaign->assigned_template))
                    {
                        $campaign->assigned_template->active = 0;
                        $campaign->assigned_template->save();
                        $client_aggy->unassignEmailTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                    }
                }
            }

            // @todo - if active == 1, set it to 0 and use aggy to update logs in Client and User
            if($campaign->active == 1)
            {
                $client_aggy = $client_aggy->updateEmailCampaign($campaign->id, request()->user()->id,'active',  false, true);
                $campaign->active = 0;
                $campaign->save();
            }

            // @todo - make details relations
            // @todo - if ttl, details record, active == 0 and softdelete and use aggy to update logs in Client and User
            $client_aggy->persist();
            Alert::success("Campaign {$campaign->name} has been updated")->flash();
            if(!$data['active']){
                Alert::warning("Campaign {$campaign->name} is not active.")->flash();
            }
            return Redirect::route('comms.email-campaigns.edit', $id);
        }
    }

    public function ec_trash($id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return Redirect::back();
        }

        $campaign = EmailCampaigns::findOrFail($id);
        $success = $campaign->deleteOrFail();
        Alert::success("Campaign '{$campaign->name}' trashed")->flash();


        return Redirect::back();
    }

    public function ec_restore(Request $request, $id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return Redirect::back();
        }
        $campaign = EmailCampaigns::withTrashed()->findOrFail($id);
        $campaign->restore();

        Alert::success("Campaign '{$campaign->name}' restored")->flash();

        return Redirect::back();
    }

    public function st_index()
    {
        $client_id = request()->user()->currentClientId();
        $is_client_user = request()->user()->isClientUser();

        $page_count = 10;
        $templates = [
            'data' => []
        ];

        $templates_model = $this->setupTemplatesObject($is_client_user, 'sms', $client_id);

        if (!empty($templates_model)) {
            $templates = $templates_model//->with('location')->with('detailsDesc')
            ->with('creator')
            ->filter(request()->only('search', 'trashed'))
                ->paginate($page_count);
        }

        return Inertia::render('Comms/SMS/Templates/SMSTemplatesIndex', [
            'title' => 'SMS Templates',
            'filters' => request()->all('search', 'trashed'),
            'templates' => $templates,
        ]);
    }

    public function st_create()
    {
        return Inertia::render('Comms/SMS/Templates/CreateSmsTemplate', [
        ]);
    }

    public function st_edit($id)
    {

        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }

        $template = SmsTemplates::find($id);
        // @todo - need to build access validation here.

        return Inertia::render('Comms/SMS/Templates/EditSmsTemplate', [
            'template' => $template
        ]);
    }

    public function st_store(Request $request)
    {
        $template = $request->validate([
                'name' => 'required',
                'markup' => 'required|max:130'
            ]
        );
        $client_id = request()->user()->currentClientId();
        // @todo - this could come in handy
        //$is_client_user = request()->user()->isClientUser();
        try {
            $template['active'] = '0';
            $template['client_id'] = $client_id;
            $template['created_by_user_id'] = $request->user()->id;
            $new_template = SmsTemplates::create($template);
            Alert::info("New Template {$template['name']} was created")->flash();
        }
        catch(\Exception $e)
        {
            Alert::error("New Template {$template['name']} could not be created")->flash();
            return redirect()->back();
        }

//        $template['created_by_user_id'] = $request->user()->id;
//        SmsTemplates::create($template);
        return Redirect::route('comms.sms-templates');
//        return Redirect::route('comms.sms-templates.edit', ['id' => $new_template->id]);

    }

    public function st_update(Request $request)
    {
        $data = $request->validate([
                'id' => 'required|exists:sms_templates,id',
                'name' => 'required',
                'markup' => 'required|max:130',
                'active' => 'sometimes',
                'client_id' => 'required|exists:clients,id',
                'created_by_user_id' => 'required',
            ]
        );

        $template = SmsTemplates::find($data['id']);
        $old_values = $template->toArray();
        $template->name = ($template->name == $data['name']) ? $template->name : $data['name'];
        $template->markup = ($template->markup == $data['markup']) ? $template->markup : $data['markup'];
        $template->active = ($template->active == $data['active']) ? $template->active : $data['active'];
        $template->save();

        ClientAggregate::retrieve($data['client_id'])
            ->updateSmsTemplate($template->id, request()->user()->id, $old_values, $template->toArray())
            ->persist();
//        $template['created_by_user_id'] = $request->user()->id;
//        SmsTemplates::create($template);
        return Redirect::route('comms.sms-templates');
    }

    public function st_trash($id)
    {
        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }

        $template = SmsTemplates::findOrFail($id);
        $success = $template->deleteOrFail();
        Alert::success("Template '{$template->name}' trashed")->flash();


        return Redirect::back();
    }

    public function st_restore(Request $request, $id)
    {
        if (!$id) {
            Alert::error("No Template ID provided")->flash();
            return Redirect::back();
        }
        $template = SmsTemplates::withTrashed()->findOrFail($id);
        $template->restore();

        Alert::success("Template '{$template->name}' restored")->flash();

        return Redirect::back();
    }

    public function sc_index()
    {
        $client_id = request()->user()->currentClientId();
        $is_client_user = request()->user()->isClientUser();

        $page_count = 10;
        $campaigns = [
            'data' => []
        ];

        $campaigns_model = $this->setupCampaignsObject($is_client_user, 'sms', $client_id);

        if (!empty($campaigns_model)) {
            $campaigns = $campaigns_model//->with('location')->with('detailsDesc')
            ->with('creator')
            ->filter(request()->only('search', 'trashed'))
            ->paginate($page_count);
        }

        return Inertia::render('Comms/SMS/Campaigns/SmsCampaignsIndex', [
            'title' => 'SMS Templates',
            'filters' => request()->all('search', 'trashed'),
            'campaigns' => $campaigns
        ]);
    }

    public function sc_create()
    {
        return Inertia::render('Comms/SMS/Campaigns/CreateSmsCampaign', [
        ]);
    }

    public function sc_edit($id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return redirect()->back();
        }

        $available_templates = [];
        $available_audiences = [];
        $campaign = SmsCampaigns::whereId($id)
            ->with('assigned_template')->with('assigned_audience')
            ->first();
        $templates = SmsTemplates::whereClientId($campaign->client_id)->whereActive('1')->get();
        foreach ($templates as $template)
        {
            $available_templates[$template->id] = $template->toArray();
        }
        $audiences = CommAudience::whereClientId($campaign->client_id)->whereActive('1')->get();
        foreach ($audiences as $audience)
        {
            $available_audiences[$audience->id] = $audience->toArray();
        }
        // @todo - need to build access validation here.
        return Inertia::render('Comms/SMS/Campaigns/EditSmsCampaign', [
            'campaign' => $campaign,
            'templates' => $available_templates,
            'audiences' => $available_audiences,
            'assigned-template' => (!is_null($campaign->assigned_template)) ? $campaign->assigned_template->value : '',
            'assigned-audience' => (!is_null($campaign->assigned_audience)) ? $campaign->assigned_audience->value : ''
        ]);
    }

    public function sc_store()
    {
        $template = request()->validate([
                'name' => 'required',
            ]
        );
        $client_id = request()->user()->currentClientId();

        try {
            $template['active'] = '0';
            $template['client_id'] = $client_id;
            $template['created_by_user_id'] = request()->user()->id;
            $new_campaign = SmsCampaigns::create($template);
            Alert::info("New Campaigns {$template['name']} was created")->flash();
        }
        catch(\Exception $e)
        {
            Alert::error("New Template {$template['name']} could not be created")->flash();
            return redirect()->back();
        }

        return Redirect::route('comms.sms-campaigns.edit', ['id' => $new_campaign->id]);
    }

    public function sc_update($id)
    {
        $data = request()->validate([
                'id' => 'required|exists:sms_campaigns,id',
                'name' => 'required',
                'active' => 'sometimes|bool',
                'schedule_date' => 'sometimes',
                'schedule' => 'sometimes',
                'sms_template_id' => 'sometimes',
                'audience_id' => 'sometimes',
                'client_id' => 'required|exists:clients,id',
                'created_by_user_id' => 'required',
            ]
        );

        $client_aggy = ClientAggregate::retrieve($data['client_id']);
        $campaign = SmsCampaigns::whereId($data['id'])
            ->with('assigned_template')->with('assigned_audience')
            ->with('schedule')->with('schedule_date')
            ->first();
        $old_values = $campaign->toArray();

        // just to start if name is different, change and use aggy to update log
        if($old_values['name'] != $data['name'])
        {
            $campaign->name = $data['name'];
            $client_aggy = $client_aggy->updateSmsCampaign($campaign->id, request()->user()->id,'name', $old_values['name'], $data['name']);
            // @todo - log this in user_details in projector
            $campaign->save();
        }

        if($data['active'])
        {
            // logic to activate the campaign, or update stuff if it is already so
            //TODO:This fails because old_values['schedule'] does not exist;
            if(is_null($old_values['schedule']) || ($old_values['schedule']['value'] != $data['schedule']))
            {
//                $campaign->schedule = $data['schedule'];
                $client_aggy = $client_aggy->updateSmsCampaign($campaign->id, request()->user()->id,'schedule', $old_values['schedule'] ?? '', $data['schedule']);
                // @todo - log this in user_details in projector
                $campaign->save();
            }
            else
            {

            }


            if($data['schedule_date'] == 'now')
            {
                $fire_date = date('Y-m-d H:i:s');
            }
            else
            {
                $fire_date = date('Y-m-d H:i:s', strtotime('now +'.$data['schedule_date']));
            }
            if($old_values['schedule_date'] != $data['schedule_date'])
            {
//                $campaign->schedule = $fire_date;
                $client_aggy = $client_aggy->updateSmsCampaign($campaign->id, request()->user()->id,'schedule_date', $old_values['schedule_date'] ?? '', $fire_date);
                // @todo - log this in user_details in projector
                $campaign->save();
            }

            if(!is_null($campaign->assigned_audience))
            {
                if($campaign->assigned_audience->value != $data['audience_id'])
                {
                    $campaign->assigned_audience->active = 0;
                    $campaign->assigned_audience->save();

                    $client_aggy = $client_aggy->unassignAudienceFromSMSCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id)
                        ->assignAudienceToSMSCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                }

            }
            else
            {
                $client_aggy = $client_aggy->assignAudienceToSMSCampaign($data['sms_template_id'], $campaign->id, request()->user()->id);
            }

            if(!is_null($campaign->assigned_template))
            {
                if($campaign->assigned_template->value != $data['sms_template_id'])
                {
                    $campaign->assigned_template->active = 0;
                    $campaign->assigned_template->save();

                    $client_aggy = $client_aggy->unassignSmsTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id)
                        ->assignSmsTemplateToCampaign($data['sms_template_id'], $campaign->id, request()->user()->id);
                }

            }
            else
            {
                $client_aggy = $client_aggy->assignSmsTemplateToCampaign($data['sms_template_id'], $campaign->id, request()->user()->id);
            }

            // active = 1 save() with aggy launchCampaign event
            // @todo - do the same for email campaign readies
            $campaign->active = 1;
            $campaign->save();
            try {
                $client_aggy = $client_aggy->launchSMSCampaign($campaign->id, $fire_date, request()->user());
                $client_aggy->persist();
                Alert::success("Campaign {$campaign->name} has been updated")->flash();
                Alert::success("Campaign {$campaign->name} is now active!")->flash();
            }
            catch(\Exception $e)
            {
                // @todo - send an Alert flash indicting the fail.
                // @todo - revert all the changes made
                Alert::error("Campaign {$old_values['name']} encountered")->flash();
                Alert::error("Campaign {$old_values['name']} was not updated")->flash();
                Alert::warning("Campaign {$old_values['name']} data was reverted and not active.")->flash();
            }


            return Redirect::route('comms.sms-campaigns');
        }
        else
        {
            // unset schedule and schedule_date if set and use aggy to update logs
            if(!is_null($campaign->schedule))
            {
                $campaign->schedule = null;
                $client_aggy = $client_aggy->updateSmsCampaign($campaign->id, request()->user()->id,'schedule', $old_values['schedule'] ?? '', $data['schedule']);
                // @todo - log this in user_details in projector
                $campaign->save();
            }

            // unset schedule_date if set and use aggy to update logs or skip
            if(!is_null($campaign->schedule_date))
            {
                $campaign->schedule_date = null;
                $client_aggy = $client_aggy->updateSmsCampaign($campaign->id, request()->user()->id,'schedule_date', $old_values['schedule_date'] ?? '', $data['schedule_date']);
                // @todo - log this in user_details in projector
                $campaign->save();
            }

            // @todo - if audience_id is not null, set it in campaign_details and use aggy to update logs in Client and Audiences and User
            if(array_key_exists('audience_id', $data))
            {
                if(!is_null($data['audience_id']))
                {
                    if($old_values['active'] == 1) {
                        // this means its getting shut off so
                        if(!is_null($campaign->assigned_audience))
                        {
                            $campaign->assigned_audience->active = 0;
                            $campaign->assigned_audience->save();
                            $client_aggy = $client_aggy->unassignAudienceFromSMSCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                        }
                    }
                    else
                    {
                        // it was never active so it's okay to assign a template if it wasn't already
                        if(!is_null($campaign->assigned_audience))
                        {
                            $campaign->assigned_audience->active = 0;
                            $campaign->assigned_audience->save();

                            if(($campaign->assigned_audience->value != $data['audience_id']))
                            {
                                $client_aggy = $client_aggy->unassignAudienceFromSMSCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);

                                if(!is_null($data['audience_id']))
                                {
                                    $client_aggy = $client_aggy->assignAudienceToSMSCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                                }

                            }
                            else
                            {
                                $client_aggy = $client_aggy->unassignAudienceFromSMSCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                            }
                        }
                        else
                        {
                            $client_aggy = $client_aggy->assignAudienceToSMSCampaign($data['audience_id'], $campaign->id, request()->user()->id);
                        }
                    }
                }
                else
                {
                    if(!is_null($campaign->assigned_audience))
                    {
                        $campaign->assigned_audience->active = 0;
                        $campaign->assigned_audience->save();
                        $client_aggy->unassignAudienceFromSMSCampaign($campaign->assigned_audience->value, $campaign->id, request()->user()->id);
                    }
                }
            }

            // @todo - if sms_template_id is not null, set it in campaign_details and use aggy to update logs in Client and Templates and User
            if(array_key_exists('sms_template_id', $data))
            {
                if((!is_null($data['sms_template_id'])))
                {
                    if($old_values['active'] == 1) {
                        // this means its getting shut off so
                        if(!is_null($campaign->assigned_template))
                        {
                            $campaign->assigned_template->active = 0;
                            $campaign->assigned_template->save();
                            $client_aggy = $client_aggy->unassignSmsTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                        }
                    }
                    else {
                        // it was never active so it's okay to assign a template if it wasn't already
                        if(!is_null($campaign->assigned_template))
                        {
                            $campaign->assigned_template->active = 0;
                            $campaign->assigned_template->save();

                            if(($campaign->assigned_template->value != $data['sms_template_id']))
                            {
                                $client_aggy = $client_aggy->unassignSmsTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);

                                if(!is_null($data['sms_template_id']))
                                {
                                    $client_aggy = $client_aggy->assignSmsTemplateToCampaign($data['sms_template_id'], $campaign->id, request()->user()->id);
                                }
                            }
                            else
                            {
                                $client_aggy = $client_aggy->unassignSmsTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                            }
                        }
                        else
                        {
                            $client_aggy = $client_aggy->assignSmsTemplateToCampaign($data['sms_template_id'], $campaign->id, request()->user()->id);
                        }

                    }
                }
                else
                {
                    if(!is_null($campaign->assigned_template))
                    {
                        $campaign->assigned_template->active = 0;
                        $campaign->assigned_template->save();
                        $client_aggy->unassignSmsTemplateFromCampaign($campaign->assigned_template->value, $campaign->id, request()->user()->id);
                    }
                }
            }

            // @todo - if active == 1, set it to 0 and use aggy to update logs in Client and User
            if($campaign->active == 1)
            {

            }

            // @todo - make details relations
            // @todo - if ttl, details record, active == 0 and softdelete and use aggy to update logs in Client and User
            $client_aggy->persist();
            Alert::success("Campaign {$campaign->name} has been updated")->flash();
            Alert::warning("Campaign {$campaign->name} is not active.")->flash();
            return Redirect::route('comms.sms-campaigns.edit', $id);
        }
    }
    public function sc_trash($id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return Redirect::back();
        }

        $campaign = SmsCampaigns::findOrFail($id);
        $success = $campaign->deleteOrFail();
        Alert::success("Campaign '{$campaign->name}' trashed")->flash();


        return Redirect::back();
    }

    public function sc_restore(Request $request, $id)
    {
        if (!$id) {
            Alert::error("No Campaign ID provided")->flash();
            return Redirect::back();
        }
        $campaign = SmsCampaigns::withTrashed()->findOrFail($id);
        $campaign->restore();

        Alert::success("Campaign '{$campaign->name}' restored")->flash();

        return Redirect::back();
    }
}
