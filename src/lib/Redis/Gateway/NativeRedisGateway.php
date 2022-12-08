<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Info;
use MateuszBieniek\IbexaDxpRedisTools\Redis\Key;

class NativeRedisGateway implements RedisGatewayInterface
{
    /** @var \Redis */
    private $client;

    public function __construct(\Redis $client)
    {
        $this->client = $client;
    }

    public function getInfo(): Info
    {
        $infoData = $this->client->info();

        return new Info((int) $infoData['maxmemory'], (int) $infoData['used_memory'], $infoData['maxmemory_policy']);
    }

    /**
     * {@inheritDoc}
     */
    public function getKeysWithoutExpiry(): iterable
    {
        $allKeyNames = $this->client->keys('*');

        foreach ($allKeyNames as $keyName) {
            if ($this->redis->ttl($keyName) === -1) {
                yield new Key($keyName, -1);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMemoryUsedByKeys(iterable $keys): int
    {
        $memory = 0;
        foreach ($keys as $key) {
            try {
                $memory += $this->client->rawCommand(
                    'MEMORY',
                    'USAGE',
                    $key->getName(),
                    'SAMPLES',
                    '0'
                );
            } catch (\RedisException $e) {
            }
        }

        return $memory;
    }
}
