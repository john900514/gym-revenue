<?php

declare(strict_types=1);

namespace App\Domain\Users\Actions;

use App\Aggregates\Clients\ClientAggregate;
use App\Domain\Users\Models\User;
use App\Http\Middleware\InjectClientId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class ImportUsers
{
    use AsAction;

    public function handle(array $data, string $client_id): bool
    {
        $result = false;
        foreach ($data as $item) {
            if ($item['extension'] === 'csv') {
                ClientAggregate::retrieve($client_id)->importUsers($item['key'], $client_id)->persist();
                $result = true;
            } else {
                Alert::error("File name: " . $item['filename'] . " doesn't meet extension requirements of '.csv'.")->flash();
            }
        }

        return $result;
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

        return $current_user->can('users.create', User::class);
    }

    public function asController(ActionRequest $request): bool
    {
        return $this->handle(
            $request->all(),
            $request->user()->client_id
        );
    }

    public function htmlResponse(bool $success): RedirectResponse
    {
        Alert::success("Users successfully imported")->flash();

        return Redirect::route('users');
    }
}
