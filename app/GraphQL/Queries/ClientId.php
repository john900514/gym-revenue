<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Domain\Users\Models\User;

final class ClientId
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): string
    {
        return User::find($args["id"])->client_id;
        // TODO implement the resolver
    }
}
