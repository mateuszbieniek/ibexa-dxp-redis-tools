<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis;

class Info
{
    /** @var int */
    private $maxMemory;

    /** @var int */
    private $usedMemory;

    /** @var string */
    private $maxMemoryPolicy;

    public const SUPPORTED_MAXMEMORY_POLICIES = [
        'volatile-lru',
        'volatile-lfu',
        'volatile-ttl',
    ];

    public function __construct(int $maxMemory, int $usedMemory, string $maxMemoryPolicy)
    {
        $this->maxMemory = $maxMemory;
        $this->usedMemory = $usedMemory;
        $this->maxMemoryPolicy = $maxMemoryPolicy;
    }

    public function getMaxMemory(): int
    {
        return $this->maxMemory;
    }

    public function getMaxMemoryPolicy(): string
    {
        return $this->maxMemoryPolicy;
    }

    public function isMaxMemoryPolicySupported(): bool
    {
        return in_array($this->getMaxMemoryPolicy(), self::SUPPORTED_MAXMEMORY_POLICIES);
    }

    public function getUsedMemory(): int
    {
        return $this->usedMemory;
    }
}
