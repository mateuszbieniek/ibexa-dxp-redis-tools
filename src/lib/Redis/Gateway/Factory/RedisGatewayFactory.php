<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\Factory;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Exception\NoAvailableGatewayException;
use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\NativeRedisGateway;
use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\PredisGateway;
use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\RedisGatewayInterface;
use Predis\Client;

class RedisGatewayFactory implements RedisGatewayFactoryInterface
{
    /**
     * @throws \MateuszBieniek\IbexaDxpRedisTools\Redis\Exception\NoAvailableGatewayException
     */
    public function getGateway(string $host, int $port): RedisGatewayInterface
    {
        if (extension_loaded('redis')) {
            $redis = new \Redis();
            try {
                $redis->connect($host, $port, 5);
            } catch (\RedisException $e) {
            }

            return new NativeRedisGateway($redis);
        }

        if (class_exists(Client::class)) {
            $redis = new Client([
                'scheme' => 'tcp',
                'host' => $host,
                'port' => $port,
            ]);
            $redis->connect();

            return new PredisGateway($redis);
        }

        throw new NoAvailableGatewayException();
    }
}
