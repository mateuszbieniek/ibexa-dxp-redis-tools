<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\ValueObject;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info;

final class InstanceStatus
{
    public const MIN_UPTIME = 14;
    public const NON_EVICTABLE_KEYS_NUMBER_THRESHOLD = 50;
    public const NON_EVICTABLE_THRESHOLD = 50;
    public const EVICTION_PERCENTAGE_THRESHOLD = 10;

    /** @var int */
    private $nonEvictableMemory;

    public function __construct(Info $info, int $nonEvictableMemory)
    {
        $this->info = $info;
        $this->nonEvictableMemory = $nonEvictableMemory;
    }

    public function getInfo(): Info
    {
        return $this->info;
    }

    public function getNonEvictableMemory(): int
    {
        return $this->nonEvictableMemory;
    }

    public function getNonEvictableMemoryPercentage(): int
    {
        $maxMemory = $this->info->getMemory()->getMaxMemory();

        return $maxMemory !== 0 ? (int) round($this->nonEvictableMemory / $maxMemory * 100) : 0;
    }

    public function getEvictedKeysPerDayPercentage(): int
    {
        $stats = $this->getInfo()->getStats();

        return (int) round(($stats->getEvictedKeysNumber() / ceil($stats->getUptimeInSeconds() / 864000)) / $stats->getNumberOfAllKeys() * 100);
    }

    public function isMinUptimeReached(): bool
    {
        return $this->info->getStats()->getUptimeInDays() >= self::MIN_UPTIME;
    }

    public function isMaxMemoryPolicySupported(): bool
    {
        return in_array($this->getInfo()->getMemory()->getMaxMemoryPolicy(), Info\Memory::SUPPORTED_MAXMEMORY_POLICIES);
    }

    public function isNonEvictableKeysNumberThresholdReached(): bool
    {
        $stats = $this->getInfo()->getStats();
        $nonEvictableKeysNumberPercentage = 100 - round($stats->getNumberOfKeysWithExpiry() / $stats->getNumberOfAllKeys() * 100);

        return $nonEvictableKeysNumberPercentage > self::NON_EVICTABLE_KEYS_NUMBER_THRESHOLD;
    }

    public function isNonEvictableMemoryThresholdReached(): bool
    {
        if ($this->getNonEvictableMemoryPercentage() < self::NON_EVICTABLE_THRESHOLD) {
            return false;
        }

        return true;
    }

    public function isEvictionPercentagePerDayThresholdReached(): bool
    {
        if ($this->getEvictedKeysPerDayPercentage() >= self::EVICTION_PERCENTAGE_THRESHOLD) {
            return true;
        }

        return false;
    }
}
