<?php

declare(strict_types=1);

namespace App\Actions\Clients\Tasks;

use App\Aggregates\Users\UserAggregate;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class TrashTask
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
            //no rules since we only accept an id route param, which is validated in the route definition
        ];
    }

    public function handle($id, User $user): void
    {
        UserAggregate::retrieve($user->id)
            ->trashTask($user->id ?? "Auto Generated", $id)
            ->persist();
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('calendar.trash', CalendarEvent::class);
    }

    public function asController(Request $request, $id)
    {
        $task = Tasks::findOrFail($id);

        $this->handle(
            $task->toArray()
        );

        Alert::success("Task '{$task->title}' sent to trash")->flash();

        return Redirect::back();
    }
}
