<?php

namespace App\Services\Imports;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeadImportService
{
    public function __construct(
        private readonly LeadSpreadsheetReader $reader,
        private readonly LeadRowMapper $mapper,
    ) {}

    public function import(string $path): array
    {
        $startedAt = hrtime(true);
        $batchSize = (int) config('import.batch_size', 1000);

        if ($batchSize < 1 || $batchSize > 1000) {
            throw new InvalidArgumentException(
                'Import batch size must be between 1 and 1000.'
            );
        }

        $imported = DB::transaction(function () use ($path, $batchSize): int {
            $batch = [];
            $imported = 0;
            $rowNumber = 1;

            foreach ($this->reader->rows($path) as $row) {
                $batch[] = $this->mapper->map($row, ++$rowNumber);

                if (count($batch) === $batchSize) {
                    DB::table('leads')->insert($batch);

                    $imported += count($batch);
                    $batch = [];
                }
            }

            // Insert the remaining rows when the final batch is incomplete.
            if ($batch !== []) {
                DB::table('leads')->insert($batch);

                $imported += count($batch);
            }

            if ($imported === 0) {
                throw new InvalidArgumentException(
                    'The file contains no data rows.'
                );
            }

            return $imported;
        });

        // Measure the complete operation, including the transaction commit.
        return [
            'imported' => $imported,
            'elapsed_seconds' => round(
                (hrtime(true) - $startedAt) / 1_000_000_000,
                3,
            ),
            'peak_memory_mb' => round(
                memory_get_peak_usage(true) / 1024 / 1024,
                2,
            ),
        ];
    }
}
