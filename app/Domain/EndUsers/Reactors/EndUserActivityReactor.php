<?php

namespace App\Domain\EndUsers\Reactors;

use App\Actions\Sms\Twilio\FireTwilioMsg;
use App\Domain\EndUsers\EndUserAggregate;
use App\Domain\EndUsers\Events\EndUserFileUploaded;
use App\Domain\EndUsers\Events\EndUserWasEmailedByRep;
use App\Domain\EndUsers\Events\EndUserWasTextMessagedByRep;
use App\Domain\EndUsers\Projections\EndUser;
use App\Domain\Users\Models\User;
use App\Mail\EndUser\EmailFromRep;
use App\Models\Utility\AppState;

use function env;

use Illuminate\Support\Facades\Mail;

class EndUserActivityReactor extends BaseEndUserReactor
{
    public function onEndUserWasEmailedByRep(EndUserWasEmailedByRep $event): void
    {
        if (($this->getModel())::class !== $event->getEntity()) {
            return;
        }
        $end_user = ($this->getModel())::find($event->aggregateRootUuid());
        if (! AppState::isSimuationMode() && ! $end_user->unsubscribe_comms) {
            Mail::to($end_user->email)->send(new EmailFromRep($event->payload, $event->aggregateRootUuid(), $event->payload['user']));
        }
    }

    public function onEndUserWasTextMessagedByRep(EndUserWasTextMessagedByRep $event): void
    {
        if (($this->getModel())::class !== $event->getEntity()) {
            return;
        }
        $end_user = ($this->getModel())::find($event->aggregateRootUuid());
        $msg = $event->payload['message'];

        if (! AppState::isSimuationMode() && ! $end_user->unsubscribe_comms) {
            FireTwilioMsg::dispatch($end_user->primary_phone, $msg)->onQueue('grp-' . env('APP_ENV') . '-jobs');
        }
    }

    public function onEndUserFileUploaded(EndUserFileUploaded $event): void
    {
        $data = $event->payload;
        $model = EndUser::find($data['entity_id']);
        $user = User::find($event->userId());

        \App\Actions\Clients\Files\CreateFile::run($data, $model, $user);
    }

    public static function getModel(): EndUser
    {
        return new EndUser();
    }

    public static function getAggregate(): EndUserAggregate
    {
        return new EndUserAggregate();
    }
}
