<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Exception\NoDatabaseException;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Memory;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Stats;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Key;
use Predis\Client;

class PredisGateway implements RedisGatewayInterface
{
    /** @var \Predis\Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getInfo(): Info
    {
        $infoData = $this->client->info();
        if (!isset($infoData['Keyspace']['db0'])) {
            throw new NoDatabaseException();
        }

        return new Info(
            new Memory(
                (int) $infoData['Memory']['maxmemory'],
                (int) $infoData['Memory']['used_memory'],
                $infoData['Memory']['maxmemory_policy']
            ),
            new Stats(
                (int) $infoData['Server']['uptime_in_days'],
                (int) $infoData['Server']['uptime_in_seconds'],
                (int) $infoData['Keyspace']['db0']['keys'],
                (int) $infoData['Keyspace']['db0']['expires'],
                (int) $infoData['Stats']['expired_keys'],
                (int) $infoData['Stats']['evicted_keys']
            )
        );
    }

    public function getKeysWithoutExpiry(): iterable
    {
        $allKeyNames = $this->client->keys('*');

        foreach ($allKeyNames as $keyName) {
            if ($this->client->ttl($keyName) === -1) {
                yield new Key($keyName, -1);
            }
        }
    }

    public function getMemoryUsedByKeys(iterable $keys): int
    {
        $memory = 0;
        foreach ($keys as $key) {
            $memory += $this->client->executeRaw([
                'MEMORY',
                'USAGE',
                $key->getName(),
                'SAMPLES',
                '0',
            ]);
        }

        return $memory;
    }
}
