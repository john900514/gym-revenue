<?php

namespace App\Actions\Clients\Notes;

use App\Aggregates\Clients\ClientAggregate;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class CreateNote
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
            'entity_id' => ['string', 'sometimes', 'nullable'],
            'entity_type' => ['string', 'sometimes', 'nullable'],
            'note' => ['string', 'sometimes', 'nullable'],
            'created_by_user_id' => ['string', 'sometimes', 'nullable'],
            'active' => ['integer', 'sometimes', 'nullable'],
            'created_at' => ['timestamp', 'sometimes', 'nullable'],
            'updated_at' => ['timestamp', 'sometimes', 'nullable'],
            'deleted_at' => ['timestamp', 'sometimes', 'nullable'],

        ];
    }

    public function handle($data, $current_user = null)
    {
        $id = (Note::max('id') ?? 0) + 1;
        $client_id = $current_user->currentClientId();
        $data['client_id'] = $client_id;

        ClientAggregate::retrieve($client_id)->createNote($current_user->id ?? "Auto Generated", $data)->persist();

        return Note::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('notes.create', Note::class);
    }

    public function asController(ActionRequest $request)
    {
        $note = $this->handle(
            $request->validated(),
            $request->user(),
        );

        Alert::success("Note '{$note->title}' was created")->flash();

//        return Redirect::route('roles');
        return Redirect::route('notes.edit', $note->id);
    }
}
