<?php

namespace App\Domain\Departments\Actions;

use App\Domain\Departments\DepartmentAggregate;
use App\Http\Middleware\InjectClientId;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class UpdateDepartment
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

    public function handle(string $id, array $payload): Department
    {
        DepartmentAggregate::retrieve($id)->update($payload)->persist();

        return Department::findOrFail($id);
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('departments.create', Department::class);
    }

    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function asController(ActionRequest $request, Department $department)
    {
        return $this->handle(
            $department->id,
            $request->validated(),
        );
    }

    public function htmlResponse(Department $department): RedirectResponse
    {
        Alert::success("Department '{$department->name}' was updated")->flash();

        return Redirect::route('departments.edit', $department->id);
    }
}
