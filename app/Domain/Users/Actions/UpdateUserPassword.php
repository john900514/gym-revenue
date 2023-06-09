<?php

declare(strict_types=1);

namespace App\Domain\Users\Actions;

use App\Domain\Users\Aggregates\UserAggregate;
use App\Domain\Users\Models\User;
use App\Domain\Users\PasswordValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

use function __;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    use AsAction;

    public function handle(User $user, array $data): void
    {
        Validator::make($data, [
            'current_password' => ['required', 'string'],
            'password' => $this->passwordRules(),
        ])->after(function ($validator) use ($user, $data): void {
            if (! isset($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
            }
        })->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        UserAggregate::retrieve($user->id)->updatePassword(bcrypt($data['password']))->persist();
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('users.update', User::class);
    }

    public function asController(ActionRequest $request, User $user): void
    {
        $this->handle(
            $user->id,
            $request->validated(),
        );
    }

    public function htmlResponse(User $user): RedirectResponse
    {
        Alert::success("Your password was successfully updated")->flash();

        return Redirect::back();
    }

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(mixed $user, array $input): void
    {
        $this->handle($user, $input);
    }
}
