<?php

namespace App\Actions\Clients\Reminders;

use App\Aggregates\Users\UserAggregate;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class DeleteReminder
{
    use AsAction;

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //no rules since we only accept an id route param, which is validated in the route definition
        ];
    }

    public function handle($current_user, $id)
    {
        $reminder = Reminder::findOrFail($id);
        $client_id = $current_user->client_id;
        UserAggregate::retrieve($current_user->id)->deleteReminder($current_user->id, $id)->persist();

        return $reminder;
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('reminders.delete', Reminder::class);
    }

    public function asController(Request $request, $id)
    {
        $reminder = $this->handle(
            $request->user(),
            $id
        );

        Alert::success("Reminder '{$reminder->name}' was deleted")->flash();

        return Redirect::route('reminders');
    }
}