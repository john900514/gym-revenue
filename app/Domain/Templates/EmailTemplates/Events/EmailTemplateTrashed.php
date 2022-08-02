<?php

namespace App\Domain\Templates\EmailTemplates\Events;

use App\Domain\Templates\EmailTemplates\Projections\EmailTemplate;
use App\StorableEvents\EntityTrashed;

class EmailTemplateTrashed extends EntityTrashed
{
    public function getEntity(): string
    {
        return EmailTemplate::class;
    }
}
