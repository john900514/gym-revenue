<?php

use App\Domain\Agreements\AgreementCategories\Projections\AgreementCategory;
use App\Domain\Agreements\Events\AgreementSigned;
use App\Domain\Clients\Actions\CreateClient;
use App\Domain\Clients\Projections\Client;
use App\Domain\EngagementEvents\Models\EngagementEvents;
use App\Domain\Users\Events\EndUserClaimedByRep;
use App\Enums\UserTypesEnum;
use Illuminate\Support\Facades\DB;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\Feature\Utilities\UserUtility;

it('should do make engagement events from creating end users', function () {
    $role = UserUtility::createRole(['name' => 'Admin']);
    $user = UserUtility::createUserWithTeam();

    $client = CreateClient::run(
        Client::factory()->raw()
    );

    $user->client_id = $client->id;
    $location = createLocation(['address1' => fake()->unique()->streetName,'client_id' => $client->id]);
    $randomNumberOfEndUsers = rand(1, 15);
    $endUsers = [];
    $numberOfEndUsersInStoredEvents = 0;
    $numberOfEndUsersInEngagementEvents = 0;
    $this->assertLessThanOrEqual(1, EngagementEvents::all()->count());

    for ($c = 0;$c < $randomNumberOfEndUsers;$c++) {
        $endU = createEndUser([
            'client_id' => $client->id,
            'home_location_id' => $location->gymrevenue_id,
            'user_type' => UserTypesEnum::LEAD,
            'opportunity' => rand(0, 3),
        ]);
        $endUsers[] = $endU->email;
    }

    $ee = EngagementEvents::all()->toArray();
    $storedEvents = DB::table('stored_events')->get()->toArray();
    foreach ($storedEvents as $event) {
        $ep = json_decode($event->event_properties, true);
        if (isset($ep['payload'])) {
            if (isset($ep['payload']['email'])) {
                $email = $ep['payload']['email'];
                if (in_array($email, $endUsers)) {
                    $numberOfEndUsersInStoredEvents++;
                    for ($c = 0;$c < count($ee);$c++) {
                        if (in_array($event->aggregate_uuid, $ee[$c])) {
                            $numberOfEndUsersInEngagementEvents++;

                            break;
                        }
                    }
                }
            }
        }
    }

    $this->assertEquals($numberOfEndUsersInStoredEvents, count($endUsers));
    $this->assertEquals(count($endUsers), $numberOfEndUsersInEngagementEvents);
});

it('should do make engagement events from signAgreement', function () {
    $role = UserUtility::createRole(['name' => 'Admin']);
    $user = UserUtility::createUserWithTeam();

    $client = CreateClient::run(
        Client::factory()->raw()
    );

    $user->client_id = $client->id;
    $location = createLocation(['address1' => fake()->unique()->streetName,'client_id' => $client->id]);
    $randomNumberOfEndUsers = rand(1, 2);
    $endUsers = [];
    $billingScheduleCreated = 0;
    $signedAgreement = 0;
    $signedAgreement2 = 0;


    $this->assertLessThanOrEqual(1, EngagementEvents::all()->count());

    for ($c = 0;$c < $randomNumberOfEndUsers;$c++) {
        $endU = createEndUser([
            'client_id' => $client->id,
            'home_location_id' => $location->gymrevenue_id,
            'user_type' => UserTypesEnum::LEAD,
            'opportunity' => rand(0, 3),
        ]);
        $endUsers[] = $endU->id;
    }

    $agreementTemplate = agreementTemplate($client, $location);
    $agreementCategory = AgreementCategory::where('client_id', $client->id)->first()->toArray();

    for ($c = 0;$c < sizeof($endUsers);$c++) {
        $agreement = makeAgreement([
            'client_id' => $client->id,
            'agreement_category_id' => $agreementCategory['id'],
            'gr_location_id' => $location->gymrevenue_id,
            'created_by' => $user->id,
            'end_user_id' => $endUsers[$c],
            'agreement_template_id' => $agreementTemplate->id,
        ]);
        signedAgreement([
            'id' => $agreement->id,
            'end_user_id' => $endUsers[$c],
        ]);
        $signedAgreement++;
    }

    $ee = EngagementEvents::all()->toArray();
    $storedEvents = DB::table('stored_events')->get()->toArray();

    foreach ($storedEvents as $event) {
        if ($event->event_class == AgreementSigned::class) {
            for ($c = 0;$c < count($ee);$c++) {
                if (in_array($event->aggregate_uuid, $ee[$c])) {
                    $signedAgreement2++;

                    break;
                }
            }
        }
    }
    $this->assertEquals($signedAgreement, $signedAgreement2);
});
it('should make engagement events from AssignEndUserToRep', function () {
    $role = UserUtility::createRole(['name' => 'Admin']);
    $user = UserUtility::createUserWithTeam();
    Bouncer::allow($role->name)->everything();
    $this->actingAs($user);
    $claimedByRep = 0;

    $client = CreateClient::run(
        Client::factory()->raw()
    );

    $user->client_id = $client->id;
    $location = createLocation(['address1' => fake()->unique()->streetName,'client_id' => $client->id]);

    $endU = createEndUser([
        'client_id' => $client->id,
        'home_location_id' => $location->gymrevenue_id,
        'user_type' => UserTypesEnum::LEAD,
        'opportunity' => rand(0, 3),
    ]);
    $this->put("data/leads/assign/".$endU->id, []);
    $storedEvents = DB::table('stored_events')->get()->toArray();

    $ee = EngagementEvents::all()->toArray();

    foreach ($storedEvents as $event) {
        if ($event->event_class == EndUserClaimedByRep::class) {
            for ($c = 0;$c < count($ee);$c++) {
                if (in_array($event->aggregate_uuid, $ee[$c])) {
                    $claimedByRep++;

                    break;
                }
            }
        }
    }
    $this->assertEquals($claimedByRep, 1);
});
it('should make engagement events from applyEndUserWasClaimedToRep', function () {
    $role = UserUtility::createRole(['name' => 'Admin']);
    $user = UserUtility::createUserWithTeam();
    Bouncer::allow($role->name)->everything();
    $this->actingAs($user);
    $applyEndUserWasClaimedToRep = 0;

    $client = CreateClient::run(
        Client::factory()->raw()
    );

    $user->client_id = $client->id;
    $location = createLocation(['address1' => fake()->unique()->streetName,'client_id' => $client->id]);

    $endU = createEndUser([
        'client_id' => $client->id,
        'home_location_id' => $location->gymrevenue_id,
        'user_type' => UserTypesEnum::LEAD,
        'opportunity' => rand(0, 3),
    ]);
    $this->put("data/leads/assign/".$endU->id, []);
    $storedEvents = DB::table('stored_events')->get()->toArray();

    $ee = EngagementEvents::all()->toArray();

    foreach ($storedEvents as $event) {
        if ($event->event_class == EndUserClaimedByRep::class) {
            for ($c = 0;$c < count($ee);$c++) {
                if (in_array($event->aggregate_uuid, $ee[$c])) {
                    $applyEndUserWasClaimedToRep++;

                    break;
                }
            }
        }
    }
    $this->assertEquals($applyEndUserWasClaimedToRep, 1);
});