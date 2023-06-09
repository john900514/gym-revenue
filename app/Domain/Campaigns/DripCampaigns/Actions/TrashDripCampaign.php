<?php

declare(strict_types=1);

namespace App\Domain\Campaigns\DripCampaigns\Actions;

use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\Domain\Campaigns\DripCampaigns\DripCampaignAggregate;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Prologue\Alerts\Facades\Alert;

class TrashDripCampaign
{
    use AsAction;

    public string $commandSignature   = 'drip-campaign:trash {id}';
    public string $commandDescription = 'Soft deletes the audience';

    public function handle(DripCampaign $dripCampaign): DripCampaign
    {
        DripCampaignAggregate::retrieve($dripCampaign->id)->trash()->persist();

        return $dripCampaign->refresh();
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('drip-campaigns.create', DripCampaign::class);
    }

    public function asController(ActionRequest $request, DripCampaign $dripCampaign): DripCampaign
    {
        return $this->handle(
            $dripCampaign->id,
        );
    }

    public function htmlResponse(DripCampaign $dripCampaign): RedirectResponse
    {
        Alert::success("Drip Campaign '{$dripCampaign->name}' was sent to trash")->flash();

        return Redirect::route('mass-comms.drip-campaigns');
    }

    public function asCommand(Command $command): void
    {
        $dripCampaign = $this->handle($command->argument('id'));
        $command->info('Soft Deleted audience ' . $dripCampaign->name);
    }
}
