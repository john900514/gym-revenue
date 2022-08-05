<?php

namespace App\Domain\Templates\SmsTemplates\Actions;

use App\Domain\Templates\SmsTemplates\Projections\SmsTemplate;
use App\Domain\Templates\SmsTemplates\SmsTemplateAggregate;
use App\Http\Middleware\InjectClientId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class UpdateSmsTemplate
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
            'markup' => ['string', 'sometimes', 'required|max:130'],
        ];
    }

    public function handle(SmsTemplate $template, array $data): SmsTemplate
    {
        SmsTemplateAggregate::retrieve($template->id)->update($data)->persist();

        return $template->refresh();
    }

    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('sms-templates.update', SmsTemplate::class);
    }

    public function asController(ActionRequest $request, SmsTemplate $smsTemplate): SmsTemplate
    {
        return $this->handle(
            $smsTemplate,
            $request->validated()
        );
    }

    public function htmlResponse(SmsTemplate $template): RedirectResponse
    {
        Alert::success("SMS Template'{$template->name}' was updated")->flash();

        return Redirect::route('comms.sms-templates.edit', $template->id);
    }
}