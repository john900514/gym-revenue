<?php

namespace App\Actions\Clients\Roles;

use App\Aggregates\Clients\ClientAggregate;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;
use Lorisleiva\Actions\Concerns\AsAction;
use Silber\Bouncer\Database\Role;


class CreateRole
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
            'title' => ['string', 'required'],
            'id' => ['integer', 'sometimes', 'nullable'],
            'ability_names' => ['array', 'sometimes'],
        ];
    }

    public function handle($data, $current_user = null)
    {
        $id = (Role::max('id') ?? 0) + 1;
        $client_id = $current_user->currentClientId();
        $data['client_id'] = $client_id;

        ClientAggregate::retrieve($client_id)->createRole($current_user->id ?? "Auto Generated", $data)->persist();

        return Role::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();
        return $current_user->can('roles.create', Role::class);
    }

    public function asController(ActionRequest $request)
    {
        $role = $this->handle(
            $request->validated(),
            $request->user(),
        );

        Alert::success("Role '{$role->title}' was created")->flash();

        return Redirect::route('roles');
    }

}
