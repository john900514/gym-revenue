<?php

declare(strict_types=1);

namespace App\Domain\Reminders\Actions;

use App\Domain\Reminders\Reminder;
use App\Domain\Users\Aggregates\UserAggregate;
use App\Http\Middleware\InjectClientId;
use App\Support\Uuid;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class CreateReminder
{
    use AsAction;

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'entity_type' => ['string', 'required'],
            'entity_id' => ['string', 'required'],
            'user_id' => ['int', 'required'],
            'name' => ['string', 'required'],
            'remind_time' => ['int', 'required'],
        ];
    }

    public function handle($data, $current_user = null): Reminder
    {
        if ($current_user !== null) {
            $client_id         = $current_user->client_id;
            $data['client_id'] = $client_id;
        }

        $id         = Uuid::new();
        $data['id'] = $id;

        UserAggregate::retrieve($data['user_id'])->createReminder($data)->persist();

        return Reminder::findOrFail($id);
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

    public function asController(ActionRequest $request)
    {
        $reminder = $this->handle(
            $request->validated(),
            $request->user(),
        );

        Alert::success("Reminder '{$reminder->name}' was created")->flash();

        return Redirect::back();
    }
}
