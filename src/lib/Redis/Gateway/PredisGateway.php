<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info;

class PredisGateway implements RedisGatewayInterface
{
    public function getInfo(): Info
    {
        // TODO: Implement getInfo() method.
    }

    public function getKeysWithoutExpiry(): iterable
    {
        // TODO: Implement getKeysWithoutExpiry() method.
    }

    public function getMemoryUsedByKeys(array $keys): int
    {
        // TODO: Implement getMemoryUsedByKeys() method.
    }
}
