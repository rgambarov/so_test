<?php

namespace App\Services\Imports;

use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactoryInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\InMemoryStrategy;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\MemoryLimit;

class SharedStringsCacheFactory implements CachingStrategyFactoryInterface
{
    public function createBestCachingStrategy(
        ?int $sharedStringsUniqueCount,
        string $tempFolder,
    ): CachingStrategyInterface {
        // Use memory caching for the supplied file and similarly sized inputs.
        if (
            $sharedStringsUniqueCount !== null
            && $sharedStringsUniqueCount <= 250_000
        ) {
            return new InMemoryStrategy($sharedStringsUniqueCount);
        }

        // Keep the default strategy for larger or unknown string tables.
        $factory = new CachingStrategyFactory(
            new MemoryLimit((string) ini_get('memory_limit'))
        );

        return $factory->createBestCachingStrategy(
            $sharedStringsUniqueCount,
            $tempFolder,
        );
    }
}
