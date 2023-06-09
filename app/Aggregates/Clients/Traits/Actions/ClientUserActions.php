<?php

declare(strict_types=1);

namespace App\Aggregates\Clients\Traits\Actions;

use App\StorableEvents\Clients\Activity\Users\ClientUserStoppedBeingImpersonated;
use App\StorableEvents\Clients\Activity\Users\ClientUserWasImpersonated;

trait ClientUserActions
{
    public function logImpersonationModeActivity(string $employee_user_id, string $impersonating_user_id)
    {
        $this->recordThat(new ClientUserWasImpersonated($this->uuid(), $employee_user_id, $impersonating_user_id, date('Y-m-d H:i:s')));

        return $this;
    }

    public function logImpersonationModeDeactivation(string $employee_user_id, string $impersonating_user_id)
    {
        $this->recordThat(new ClientUserStoppedBeingImpersonated($this->uuid(), $employee_user_id, $impersonating_user_id, date('Y-m-d H:i:s')));

        return $this;
    }
}
