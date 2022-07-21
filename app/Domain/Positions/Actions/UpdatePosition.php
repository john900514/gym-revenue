<?php

namespace App\Domain\Positions\Actions;

use App\Domain\Positions\PositionAggregate;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class UpdatePosition
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
            'name' => ['string', 'required'],
        ];
    }

    public function handle(string $id, array $payload): Position
    {
        PositionAggregate::retrieve($id)->update($payload)->persist();

        return Position::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('positions.update', Position::class);
    }

    public function asController(ActionRequest $request, Position $position)
    {
        return $this->handle(
            $position->id,
            $request->validated(),
        );
    }

    public function htmlResponse(Position $position): RedirectResponse
    {
        Alert::success("Position '{$position->name}' was updated")->flash();

        return Redirect::route('positions.edit', $position->id);
    }
}