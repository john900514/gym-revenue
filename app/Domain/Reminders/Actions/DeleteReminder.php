<?php

declare(strict_types=1);

namespace App\Domain\Reminders\Actions;

use App\Domain\Reminders\Reminder;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Http\Middleware\InjectClientId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class DeleteReminder
{
    use AsAction;

    public function handle(Reminder $reminder): Reminder
    {
        UserAggregate::retrieve($reminder->user_id)->deleteReminder($reminder->id)->persist();

        return $reminder;
    }

    /**
     * @return string[]
     */
    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    //TODO: implement real authorization
    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function asController(Reminder $reminder): RedirectResponse
    {
        $this->handle(
            $reminder,
        );

        Alert::success("Reminder deleted")->flash();

        return Redirect::back();
    }
}
