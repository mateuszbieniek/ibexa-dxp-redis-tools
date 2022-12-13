<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway;

use MateuszBieniek\IbexaDxpRedisTools\Redis\ValueObject\Info;

interface RedisGatewayInterface
{
    public function getInfo(): Info;

    /**
     * @return \MateuszBieniek\IbexaDxpRedisTools\Redis\ValueObject\Key[]
     */
    public function getKeysWithoutExpiry(): iterable;

    /**
     * @param \MateuszBieniek\IbexaDxpRedisTools\Redis\ValueObject\Key[] $keys
     */
    public function getMemoryUsedByKeys(array $keys): int;
}
