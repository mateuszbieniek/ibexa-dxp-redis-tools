<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Key;

interface RedisGatewayInterface
{
    public function getInfo(): Info;

    /**
     * @return \MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Key[]
     */
    public function getKeysWithoutExpiry(): iterable;

    /**
     * @param \MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Key[] $keys
     */
    public function getMemoryUsedByKeys(iterable $keys): int;

    public function getMembers(Key $set): iterable;

    public function keyExists(Key $key): bool;
}
