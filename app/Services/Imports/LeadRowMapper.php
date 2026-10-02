<?php

namespace App\Services\Imports;

use DateTimeInterface;
use InvalidArgumentException;

class LeadRowMapper
{
    public function map(array $row, int $rowNumber): array
    {
        $data = [];

        foreach (config('import.headers') as $field) {
            $value = $row[$field] ?? null;

            $data[$field] = match ($field) {
                'created_at', 'next_contact_at' => $this->date(
                    $value,
                    $field,
                    $rowNumber,
                ),
                'budget_uah' => $this->budget($value, $rowNumber),
                default => $this->text($value),
            };
        }

        foreach (['external_id', 'created_at'] as $field) {
            if ($data[$field] === null) {
                throw new InvalidArgumentException(
                    "Row {$rowNumber}: {$field} is required."
                );
            }
        }

        return $data;
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function date(
        mixed $value,
        string $field,
        int $rowNumber,
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        // OpenSpout converts date-formatted Excel cells to DateTime objects.
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        throw new InvalidArgumentException(
            "Row {$rowNumber}: {$field} must be an Excel date."
        );
    }

    private function budget(mixed $value, int $rowNumber): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException(
                "Row {$rowNumber}: budget_uah must be numeric."
            );
        }

        $amount = (float) $value;

        // Match the range of DECIMAL(14, 2), including rounding.
        if (! is_finite($amount) || abs(round($amount, 2)) >= 1_000_000_000_000) {
            throw new InvalidArgumentException(
                "Row {$rowNumber}: budget_uah is outside the supported range."
            );
        }

        return number_format($amount, 2, '.', '');
    }
}
