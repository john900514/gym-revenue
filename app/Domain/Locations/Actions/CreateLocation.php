<?php

declare(strict_types=1);

namespace App\Domain\Locations\Actions;

use App\Actions\GymRevAction;
use App\Domain\Locations\Enums\LocationType;
use App\Domain\Locations\LocationAggregate;
use App\Domain\Locations\Projections\Location;
use App\Enums\StatesEnum;
use App\Rules\AddressCity;
use App\Rules\AddressLine;
use App\Rules\AddressState;
use App\Rules\AddressZip;
use App\Support\Uuid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;

class CreateLocation extends GymRevAction
{
    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:50'],
            'city' => ['required', 'max:30', new AddressCity()],
            'state' => ['required', 'size:2', new Enum(StatesEnum::class), new AddressState()],
            'client_id' => ['required', 'exists:clients,id'],
            'address1' => ['required','max:200', new AddressLine()],
            'address2' => ['sometimes', 'nullable','max:200'],
            'latitude' => ['required', 'numeric', 'regex:/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
            'longitude' => ['required', 'numeric', 'regex:/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/'],
            'zip' => ['required', 'size:5', new AddressZip()],
            'phone' => [],
            'poc_first' => ['sometimes', 'nullable', 'string', 'max:50'],
            'poc_last' => ['sometimes', 'nullable', 'string', 'max:50'],
            'poc_phone' => ['sometimes', 'nullable', 'string', 'size:10'],
            'close_date' => [],
            'location_no' => ['required', 'max:10'],
            'gymrevenue_id' => ['sometimes', 'nullable', 'unique:locations,gymrevenue_id'],
            'opened_at' => [],
            'default_team_id' => ['sometimes', 'nullable', 'exists:teams,id'],
            'shouldCreateTeam' => ['sometimes', 'boolean'],
            'location_type' => ['required', new Enum(LocationType::class)],
            'presale_opened_at' => ['sometimes'],
            'presale_started_at' => [],
            'capacity' => ['required','integer'],
        ];
    }

    /**
     * Validate the address provided using USPS API after main rules
     * Which also sends back correct address1, city and state
     *
     */
    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        /**
         * @TODO: Send the suggestion data back to UI, and display to the User.
         * They can make a choice (confirm/cancel), and have it update if confirmed
         */
        session()->forget('address_validation');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<array<string, mixed>>
     */
    public function mapArgsToHandle(array $args): array
    {
        return [$args['location']];
    }

    /**
     * @param array<string, mixed> $data
     *
     */
    public function handle(array $data): Location
    {
        $id                    = Uuid::get();//we should use uuid here
        $gymrevenue_id         = GenerateGymRevenueId::run($data['client_id']);
        $data['gymrevenue_id'] = $gymrevenue_id;
        LocationAggregate::retrieve($id)->create($data)->persist();

        return Location::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('locations.create', Location::class);
    }

    public function asController(ActionRequest $request): Location
    {
        return $this->handle(
            $request->validated()
        );
    }

    public function htmlResponse(Location $location): RedirectResponse
    {
        Alert::success("Locataion '{$location->name}' was created")->flash();

        return Redirect::route('locations.edit', $location->id);
    }

    public function getValidationAttributes(): array
    {
        return [
            'addres1' => 'address line 1',
        ];
    }
}
