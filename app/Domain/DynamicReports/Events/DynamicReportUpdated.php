<?php

declare(strict_types=1);

namespace App\Domain\DynamicReports\Events;

use App\Models\DynamicReport;
use App\StorableEvents\EntityCreated;

class DynamicReportUpdated extends EntityCreated
{
    public function getEntity(): string
    {
        return DynamicReport::class;
    }
}
