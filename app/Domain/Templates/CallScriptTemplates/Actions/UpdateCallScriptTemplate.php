<?php

namespace App\Domain\Templates\CallScriptTemplates\Actions;

use App\Domain\Templates\CallScriptTemplates\CallScriptTemplateAggregate;
use App\Domain\Templates\CallScriptTemplates\Projections\CallScriptTemplate;
use App\Http\Middleware\InjectClientId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class UpdateCallScriptTemplate
{
    use AsAction;

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'sometimes'],
            'subject' => ['string', 'sometimes'],
            'markup' => ['string', 'sometimes'],
            'json' => ['sometimes', 'array'],
        ];
    }

    public function handle(CallScriptTemplate $template, array $data): CallScriptTemplate
    {
        CallScriptTemplateAggregate::retrieve($template->id)
            ->update($data)
            ->persist();

        return $template->refresh();
    }

    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('call-templates.update', CallScriptTemplate::class);
    }

    public function asController(ActionRequest $request, CallScriptTemplate $callscriptTemplate): CallScriptTemplate
    {
        return $this->handle(
            $callscriptTemplate,
            $request->validated()
        );
    }

    public function htmlResponse(CallScriptTemplate $template): RedirectResponse
    {
        Alert::success("CallScript Template'{$template->name}' was updated")->flash();

        return Redirect::route('mass-comms.callscript-templates.edit', $template->id);
    }
}
