<?php

namespace App\Domain\Audiences\Actions;

use App\Domain\Audiences\Audience;
use App\Domain\Audiences\AudienceAggregate;
//use App\Domain\Campaigns\DripCampaigns\DripCampaign;
use App\Domain\Clients\Projections\Client;
use App\Domain\LeadTypes\LeadType;
use App\Domain\Users\Models\User;
use App\Http\Middleware\InjectClientId;
use App\Support\Uuid;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAudience
{
    use AsAction;

    public string $commandSignature = 'audience:create';
    public string $commandDescription = 'Creates a Audience with the given name.';

    public function handle(array $data): Audience
    {
        $id = Uuid::new();

        AudienceAggregate::retrieve($id)->create($data)->persist();

        return Audience::findOrFail($id);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'entity' => ['sometimes', 'string'],
            'filters' => ['required', 'array', 'min:1'],
            'client_id' => ['string', 'required'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge(['entity' => User::class]);
    }

    public function asCommand(Command $command): void
    {
        $audience = $this->handle([
            'client_id' => $this->getClient($command),
            'name' => $command->ask("Enter Audience Name"),
            'entity' => User::class,
        ]);

        $command->info('Created Audience ' . $audience->name);
    }

    public function getControllerMiddleware(): array
    {
        return [InjectClientId::class];
    }

    public function asController(ActionRequest $request): Audience
    {
        $data = $request->validated();
        $lead_type_ids = [];
        $member_type_ids = [];
        foreach ($data['filters']['type_id'] as $type_id) {
            if (LeadType::whereId($type_id)->exists()) {
                $lead_type_ids[] = $type_id;
            } else {
                $member_type_ids[] = $type_id;
            }
        }

        $data['filters']['lead_type_id'] = $lead_type_ids;
        $data['filters']['member_type_id'] = $member_type_ids;

        unset($data['filters']['type_id']);

        return $this->handle(
            $data
        );
    }

    private function getClient($command): ?string
    {
        $clients = [];
        $client_ids = [];
        $db_clients = Client::whereActive(1)->get();

        foreach ($db_clients as $idx => $client) {
            $clients[$idx + 1] = $client->name;
            $client_ids[$idx + 1] = $client->id;
        }

        $command->info('Select a client');
        foreach ($clients as $idx => $name) {
            $command->warn("[{$idx}] {$name}");
        }
        $client_choice = $command->ask("Select a client");

        if ($client_choice <= count($db_clients)) {
            $client_id = $client_ids[$client_choice];
            $command->info($clients[$client_choice]);
        }

        return $client_id;
    }
}
