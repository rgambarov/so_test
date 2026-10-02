<?php

namespace App\Services\Imports;

use LogicException;

class LeadImportService
{
    public function __construct(private readonly LeadSpreadsheetReader $reader) {}

    /** Starter only: no writes until the import implementation is added. */
    public function import(string $path): array
    {
        // TODO: normalize and validate rows from $this->reader->rows($path).
        // TODO: define the duplicate external_id policy explicitly.
        // TODO: DB::transaction + batch insert (config('import.batch_size')).
        // TODO: flush the final partial batch; never silently skip invalid rows.
        // TODO: return imported count, elapsed wall time and peak memory.
        // TODO: benchmark the supplied 100,000 rows through PHP-FPM at 30 seconds.
        throw new LogicException('Import is not implemented yet. See docs/NEXT_STEPS.md.');
    }
}
