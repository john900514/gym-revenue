<?php

namespace App\Actions\Clients\Files;

use App\Aggregates\Clients\ClientAggregate;
use App\Aggregates\Clients\FileAggregate;
use App\Aggregates\Users\UserAggregate;
use App\Models\Clients\Location;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Prologue\Alerts\Facades\Alert;
use App\Models\Clients\Client;
use App\Models\Team;
use App\Models\User;
use Laravel\Jetstream\Jetstream;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;


class CreateFiles
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
            '*.id' => 'uuid|required',
            '*.filename' => 'max:255|required',
            '*.original_filename' => 'max:255|required',
            '*.extension' => 'required|string|min:3|max:4',
            '*.bucket' => 'max:255|required',
            '*.key' => 'max:255|required',
//            '*.is_public' =>'boolean|required',
            '*.size' => 'integer|min:1|required',//TODO: add max size
            '*.client_id' => 'exists:clients,id|required',
            '*.user_id' => 'nullable|exists:users,id'
        ];
    }

    public function handle($data, $current_user = null)
    {
        $files = [];
        foreach( $data as $file){
            $files[] = CreateFile::run($file);
        }

        return $files;
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();
        return $current_user->can('files.create', $current_user->currentTeam()->first());
    }

    public function asController(ActionRequest $request)
    {

        $files = $this->handle(
            $request->validated(),
            $request->user(),
        );

        $fileCount = count($files);
        Alert::success("{$fileCount} Files created")->flash();

//        return Redirect::route('files');
        return Redirect::back();

    }
}