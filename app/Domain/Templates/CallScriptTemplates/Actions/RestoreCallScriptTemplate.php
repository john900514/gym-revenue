<?php

declare(strict_types=1);

namespace App\Domain\Templates\CallScriptTemplates\Actions;

use App\Domain\Templates\CallScriptTemplates\CallScriptTemplateAggregate;
use App\Domain\Templates\CallScriptTemplates\Projections\CallScriptTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class RestoreCallScriptTemplate
{
    use AsAction;

    public function handle(CallScriptTemplate $call_template): CallScriptTemplate
    {
        CallScriptTemplateAggregate::retrieve($call_template->id)->restore()->persist();

        return $call_template->refresh();
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('call-templates.restore', CallScriptTemplate::class);
    }

    public function asController(ActionRequest $request, CallScriptTemplate $call_template): CallScriptTemplate
    {
        return $this->handle(
            $call_template,
        );
    }

    public function htmlResponse(CallScriptTemplate $call_template): RedirectResponse
    {
        Alert::success("CallScript Template'{$call_template->name}' was restored")->flash();

        return Redirect::route('mass-comms.call-templates');
    }
}
