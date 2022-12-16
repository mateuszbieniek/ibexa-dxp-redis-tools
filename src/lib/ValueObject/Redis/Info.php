<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Memory;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Stats;

final class Info
{
    /** @var \MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Memory */
    private $memory;

    /** @var \MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Stats */
    private $stats;

    public function __construct(
        Memory $memory,
        Stats $stats
    ) {
        $this->memory = $memory;
        $this->stats = $stats;
    }

    public function getMemory(): Memory
    {
        return $this->memory;
    }

    public function getStats(): Stats
    {
        return $this->stats;
    }
}
