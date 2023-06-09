<?php

declare(strict_types=1);

namespace App\Domain\Audiences\Actions;

use App\Domain\Audiences\Audience;
use App\Domain\Audiences\AudienceAggregate;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class TrashAudience
{
    use AsAction;

    public string $commandSignature   = 'audience:trash {id}';
    public string $commandDescription = 'Soft deletes the audience';

    public function handle(Audience $audience): Audience
    {
        AudienceAggregate::retrieve($audience->id)->trash()->persist();

        return $audience->refresh();
    }

    public function authorize(ActionRequest $request): bool
    {
        $current_user = $request->user();

        return $current_user->can('*');
    }

    public function asCommand(Command $command): void
    {
        $audience = $this->handle($command->argument('id'));
        $command->info('Soft Deleted audience ' . $audience->name);
    }
}
