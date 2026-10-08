<?php

namespace App\Services\Imports;

use DateTimeInterface;
use Generator;
use InvalidArgumentException;
use OpenSpout\Reader\XLSX\Reader;

class LeadSpreadsheetReader
{
    /** Streams the first sheet; closes the reader even when iteration fails. */
    public function rows(string $path): Generator
    {
        $reader = new Reader(
            cachingStrategyFactory: new SharedStringsCacheFactory(),
        );
        $reader->open($path);
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $headerRead = false;
                foreach ($sheet->getRowIterator() as $row) {
                    $values = $row->toArray();
                    if (! $headerRead) {
                        $this->validateHeaders($values);
                        $headerRead = true;

                        continue;
                    }
                    $headers = config('import.headers');
                    if (count($values) > count($headers)) {
                        throw new InvalidArgumentException('Unexpected extra columns.');
                    }
                    $values = array_pad($values, count($headers), null);
                    yield array_combine($headers, $values);
                }
                if (! $headerRead) {
                    throw new InvalidArgumentException('The first sheet has no header row.');
                }
                break;
            }
        } finally {
            $reader->close();
        }
    }

    public function preview(string $path, int $limit = 5): array
    {
        $result = [];
        foreach ($this->rows($path) as $row) {
            $result[] = array_map(
                fn ($value) => $value instanceof DateTimeInterface
                    ? $value->format('Y-m-d H:i:s') : $value,
                $row,
            );
            if (count($result) >= max(1, $limit)) {
                break;
            }
        }

        return $result;
    }

    private function validateHeaders(array $values): void
    {
        $actual = array_map(fn ($value) => trim((string) $value), $values);
        if ($actual !== config('import.headers')) {
            throw new InvalidArgumentException('Headers or their order do not match the expected 15 columns.');
        }
    }
}
