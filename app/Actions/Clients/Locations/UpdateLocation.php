<?php

namespace App\Actions\Clients\Locations;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Location;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class UpdateLocation
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
            'poc_last' => ['sometimes'],
            'name' => ['required', 'max:50'],
            'city' => ['required', 'max:30'],
            'state' => ['required', 'size:2'],
            'client_id' => ['required', 'exists:clients,id'],
            'address1' => ['required','max:200'],
            'address2' => [],
            'zip' => ['required', 'size:5'],
            'phone' => [],
            'poc_first' => [],
            'poc_phone' => [],
            'open_date' => [],
            'close_date' => [],
            'location_no' => ['required', 'max:50', 'exists:locations,location_no'],
            'gymrevenue_id' => ['sometimes', 'nullable', 'exists:locations,gymrevenue_id'],
            'default_team_id' => ['sometimes', 'nullable', 'exists:teams,id'],
        ];
    }

    public function handle($data, $current_user = null)
    {
        ClientAggregate::retrieve($data['client_id'])->updateLocation($current_user->id ?? "Auto Generated", $data)->persist();

        return Location::find($data['id']);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('locations.update', Location::class);
    }

    public function asController(ActionRequest $request, $id)
    {
        $data = $request->validated();
        $data['id'] = $id;
        $location = $this->handle(
            $data,
            $request->user(),
        );

        Alert::success("Location '{$location->name}' was updated")->flash();

//        return Redirect::route('users');
        return Redirect::back();
    }
}
