<?php

declare(strict_types=1);

namespace App\Domain\Files\Actions;

use App\Actions\GymRevAction;
use App\Aggregates\Clients\FileAggregate;
use App\Models\File;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;

class RestoreFile extends GymRevAction
{
    /**
     * @param array<string, mixed> $data
     *
     */
    public function handle(array $data): File
    {
        $id = $data['id'];

        FileAggregate::retrieve($id)->restore($data['user_id'] ?? "Auto Generated")->persist();

        return File::findOrFail($id);
    }

    /**
     * @param $args
     *
     * @return array<array<string, mixed>>
     */
    public function mapArgsToHandle($args): array
    {
        return [$args];
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('files.trash', File::class);
    }

    public function asController(ActionRequest $request, $id)
    {
        $user    = $request->user();
        $user_id = $user->id ?? null;
        $data    = [
            'id' => $id,
            'user_id' => $user_id,
        ];
        $file    = $this->handle(
            $data,
        );

        Alert::success("File '{$file->filename}' was restored")->flash();

//        return Redirect::route('files');
        return Redirect::back();
    }
}
