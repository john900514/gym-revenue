<?php

declare(strict_types=1);

namespace App\Domain\Users\Actions;

use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\User;
use App\Http\Middleware\InjectClientId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class SetCustomUserCrudColumns
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
            'table' => ['required', 'string','max:50'],
            'columns' => ['required', 'array', 'min:1', 'max:7'],
        ];
    }

    public function handle(array $data, User $user): User
    {
        UserAggregate::retrieve((string) $user->id)->setCustomCrudColumns($data['table'], $data['columns'])->persist();

        return $user->refresh();
    }

    /**
     * @return string[]
     */
    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function authorize(ActionRequest $_): bool
    {
//        $current_user = $request->user();
        return true;
    }

    public function asController(ActionRequest $request): User
    {
        $data = $request->validated();

        return $this->handle(
            $data,
            $request->user()
        );
    }

    public function htmlResponse(User $_): RedirectResponse
    {
        Alert::success("CRUD columns updated")->flash();

        return Redirect::back();
    }
}
