<?php

declare(strict_types=1);

namespace App\Domain\Folders\Actions;

use App\Actions\GymRevAction;
use App\Domain\Folders\FolderAggregate;
use App\Http\Middleware\InjectClientId;
use App\Models\Folder;
use App\Support\Uuid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;

class CreateFolder extends GymRevAction
{
    /**
     * @param array<string, mixed> $payload
     *
     */
    public function handle(array $payload): Folder
    {
        $id = Uuid::get();

        FolderAggregate::retrieve($id)->create($payload)->persist();

        return Folder::findOrFail($id);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'nullable','string', 'max:255', 'exists:clients,id'],
            'name' => ['required', 'max:50'],
        ];
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array
     */
    public function mapArgsToHandle(array $args): array
    {
        return [$args];
    }

    /**
     * @return string[]
     */
    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('folders.create', Folder::class);
    }

    public function asController(ActionRequest $request): Folder
    {
        return $this->handle(
            $request->validated()
        );
    }

    public function htmlResponse(Folder $folder): RedirectResponse
    {
        Alert::success("Folder '{$folder->name}' was created")->flash();

        //return Redirect::route('folder.edit', $folder->id);
        return Redirect::back();
    }

    public function create($user, array $input): Folder
    {
        return $this->handle($input);
    }
}
