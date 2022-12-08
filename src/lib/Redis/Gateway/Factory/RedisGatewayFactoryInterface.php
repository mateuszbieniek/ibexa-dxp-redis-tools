<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\Factory;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\RedisGatewayInterface;

interface RedisGatewayFactoryInterface
{
    public function getGateway(string $host, int $port): RedisGatewayInterface;
}
