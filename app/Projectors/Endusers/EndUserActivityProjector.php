<?php

namespace App\Projectors\Endusers;

use App\Models\Clients\Features\CommAudience;
use App\Models\Clients\Features\Memberships\TrialMembershipType;
use App\Models\Endusers\AudienceMember;
use App\Models\Endusers\Lead;
use App\Models\Endusers\LeadDetails;
use App\Models\Endusers\TrialMembership;
use App\Models\User;
use App\StorableEvents\Endusers\LeadClaimedByRep;
use App\StorableEvents\Endusers\LeadServicesSet;
use App\StorableEvents\Endusers\LeadWasCalledByRep;
use App\StorableEvents\Endusers\LeadWasDeleted;
use App\StorableEvents\Endusers\LeadWasEmailedByRep;
use App\StorableEvents\Endusers\LeadWasTextMessagedByRep;
use App\StorableEvents\Endusers\ManualLeadMade;
use App\StorableEvents\Endusers\NewLeadMade;
use App\StorableEvents\Endusers\SubscribedToAudience;
use App\StorableEvents\Endusers\TrialMembershipAdded;
use App\StorableEvents\Endusers\TrialMembershipUsed;
use App\StorableEvents\Endusers\UpdateLead;
use Carbon\Carbon;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class EndUserActivityProjector extends Projector
{
    public function onNewLeadMade(NewLeadMade $event)
    {
        $lead = Lead::create($event->lead);

        foreach ($event->lead['services'] ?? [] as $service_id) {
            LeadDetails::create([
                    'lead_id' => $event->aggregateRootUuid(),
                    'client_id' => $lead->client_id,
                    'field' => 'service_id',
                    'value' => $service_id
                ]
            );
        }
    }

    public function onManualLeadMade(ManualLeadMade $event)
    {
        $user = User::find($event->user);
        LeadDetails::create([
            'lead_id' => $event->id,
            'client_id' => $event->lead['client_id'],
            'field' => 'manual_create',
            'value' => $user->email,
        ]);
    }

    public function onUpdateLead(UpdateLead $event)
    {
        $lead = Lead::findOrFail($event->id);
        $old_data = $lead->toArray();
        $user = User::find($event->user);
        $lead->updateOrFail($event->lead);
        LeadDetails::whereLeadId($event->id)->whereField('service_id')->delete();
        foreach ($event->lead['services'] ?? [] as $service_id) {
            LeadDetails::firstOrCreate([
                    'lead_id' => $event->aggregateRootUuid(),
                    'client_id' => $lead->client_id,
                    'field' => 'service_id',
                    'value' => $service_id
                ]
            );
        }

        LeadDetails::create([
            'lead_id' => $event->id,
            'client_id' => $lead->client_id,
            'field' => 'updated',
            'value' => $user->email,
            'misc' => [
                'old_data' => $old_data,
                'new_data' => $event->lead,
                'user' => $event->user
            ]
        ]);
    }

    public function onLeadClaimedByRep(LeadClaimedByRep $event)
    {
        $lead = LeadDetails::firstOrCreate([
            'client_id' => $event->client,
            'lead_id' => $event->lead,
            'field' => 'claimed',
            'value' => $event->user
        ]);

        $misc = $lead->misc;
        if (!is_array($misc)) {
            $misc = [];
        }

        if (!array_key_exists('claim_date', $misc)) {
            $misc['claim_date'] = date('Y-m-d');
        }

        $user = User::find($event->user);
        $misc['user_id'] = $user->email;
        $lead->misc = $misc;
        $lead->save();
    }

    public function onLeadWasEmailedByRep(LeadWasEmailedByRep $event)
    {
        $lead = Lead::find($event->lead);
        $user = User::find($event->user);

        $misc = $event->data;
        $misc['user_email'] = $user->email;
        LeadDetails::firstOrCreate([
            'client_id' => $lead->client_id,
            'lead_id' => $event->lead,
            'field' => 'emailed_by_rep',
            'value' => $event->user,
            'misc' => $misc,
        ]);
    }

    public function onLeadWasTextMessagedByRep(LeadWasTextMessagedByRep $event)
    {
        $lead = Lead::find($event->lead);
        $user = User::find($event->user);

        $misc = $event->data;
        $misc['user_email'] = $user->email;
        LeadDetails::firstOrCreate([
            'client_id' => $lead->client_id,
            'lead_id' => $event->lead,
            'field' => 'sms_by_rep',
            'value' => $event->user,
            'misc' => $misc,
        ]);

    }

    public function onLeadWasCalledByRep(LeadWasCalledByRep $event)
    {
        $lead = Lead::find($event->lead);
        $user = User::find($event->user);

        $misc = $event->data;
        $misc['user_email'] = $user->email;
        LeadDetails::firstOrCreate([
            'client_id' => $lead->client_id,
            'lead_id' => $event->lead,
            'field' => 'called_by_rep',
            'value' => $event->user,
            'misc' => $misc,
        ]);
    }

    public function onSubscribedToAudience(SubscribedToAudience $event)
    {
        $audience_record = CommAudience::whereClientId($event->client)
            ->whereSlug($event->audience)->whereActive(1)->first();

        if (!is_null($audience_record)) {
            // add a new record to audience_members
            $audience_member_record = AudienceMember::firstOrCreate([
                'client_id' => $event->client,
                'audience_id' => $audience_record->id,
                'entity_id' => $event->user,
                'entity_type' => $event->entity,
                'subscribed' => true,
                'dnc' => false
            ]);

            // add a new record to entity's details
            $entity_model = new $event->entity;
            $details_class = $entity_model::getDetailsTable();
            $details_model = new $details_class();
            $details_model->firstOrCreate([
                'client_id' => $event->client,
                $details_model->fk => $event->user,
                'field' => 'audience_subscribed',
                'value' => $audience_record->id,
                'misc' => [
                    'date' => date('Y-m-d'),
                    'audience_member_record' => $audience_member_record->id
                ],
            ]);
        }

    }


    //primarily just unseed for seeder. couldn't get to work otherwise.
    public function onLeadServicesSet(LeadServicesSet $event)
    {
//        $LeadDetails::firstOrCreate();
        $lead = Lead::find($event->aggregateRootUuid());

        LeadDetails::whereLeadId($lead->id)->whereField('service_id')->delete();

        foreach ($event->serviceIds ?? [] as $service_id) {
            LeadDetails::firstOrCreate([
                    'lead_id' => $event->aggregateRootUuid(),
                    'client_id' => $lead->client_id,
                    'field' => 'service_id',
                    'value' => $service_id
                ]
            );

        }
    }

    public function onLeadWasDeleted(LeadWasDeleted $event)
    {
        $lead = Lead::findOrFail($event->lead);
        $client_id = $lead->client_id;
        $success = $lead->deleteOrFail();

        $uare = $event->user;

        LeadDetails::create([
            'client_id' => $client_id,
            'lead_id' => $event->lead,
            'field' => 'softdelete',
            'value' => $uare,
            'misc' => ['userid' => $uare]
        ]);

    }

    public function onTrialMembershipAdded(TrialMembershipAdded $event)
    {
        $lead = Lead::findOrFail($event->lead);
        $trial = TrialMembershipType::find($event->trial);
        $client_id = $lead->client_id;
        TrialMembership::create([
            'client_id' => $client_id,
            'type_id' => $event->trial,
            'lead_id' => $event->lead,
            'start_date' => $event->date,
            'expiry_date' => Carbon::instance(new \DateTime($event->date))->addDays($trial->trial_length),
            'club_id' => $lead->gr_location_id,
            'active' => 1
        ]);
    }

    public function onTrialMembershipUsed(TrialMembershipUsed $event)
    {
        $lead = Lead::findOrFail($event->lead);

        LeadDetails::create([
            'client_id' => $event->client,
            'lead_id' => $event->lead,
            'field' => 'trial-used',
            'value' => $event->trial,
            'misc' => ['trial_id' => $event->trial, 'date' => $event->date, 'client' => $event->client]
        ]);
    }
}
