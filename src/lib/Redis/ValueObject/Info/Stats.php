<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\ValueObject\Info;

final class Stats
{
    /** @var int */
    private $uptime;

    /** @var int */
    private $expiredKeys;

    /** @var int */
    private $evictedKeys;

    /** @var int */
    private $allKeys;

    /** @var int */
    private $expiringKeys;

    public function __construct(
        int $uptime,
        int $allKeys,
        int $expiringKeys,
        int $expiredKeys,
        int $evictedKeys
    ) {
        $this->uptime = $uptime;
        $this->allKeys = $allKeys;
        $this->expiringKeys = $expiringKeys;
        $this->expiredKeys = $expiredKeys;
        $this->evictedKeys = $evictedKeys;
    }

    public function getUptimeInDays(): int
    {
        return $this->uptime;
    }

    public function getNumberOfAllKeys(): int
    {
        return $this->allKeys;
    }

    public function getNumberOfKeysWithExpiry(): int
    {
        return $this->expiringKeys;
    }

    public function getExpiredKeysNumber(): int
    {
        return $this->expiredKeys;
    }

    public function getEvictedKeysNumber(): int
    {
        return $this->evictedKeys;
    }
}
