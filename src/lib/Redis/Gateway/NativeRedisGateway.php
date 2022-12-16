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

class NativeRedisGateway implements RedisGatewayInterface
{
    /** @var \Redis */
    private $client;

    public function __construct(\Redis $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \RedisException
     */
    public function getInfo(): Info
    {
        $infoData = $this->client->info();
        if (!isset($infoData['db0'])) {
            throw new \RedisException();
        }

        $keyspace = $this->processKeyspace($infoData['db0']);

        return new Info(
            new Memory(
                (int) $infoData['maxmemory'],
                (int) $infoData['used_memory'],
                $infoData['maxmemory_policy']
            ),
            new Stats(
                (int) $infoData['uptime_in_days'],
                (int) $infoData['uptime_in_seconds'],
                (int) $keyspace['keys'],
                (int) $keyspace['expires'],
                (int) $infoData['expired_keys'],
                (int) $infoData['evicted_keys']
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getKeysWithoutExpiry(): iterable
    {
        $allKeyNames = $this->client->keys('*');

        foreach ($allKeyNames as $keyName) {
            if ($this->client->ttl($keyName) === -1) {
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
            } catch (NoDatabaseException $e) {
            }
        }

        return $memory;
    }

    private function processKeyspace(string $keyspaceData): array
    {
        $keyspace = [];
        array_map(static function (string $valueString) use (&$keyspace) {
            list($key, $value) = explode('=', $valueString);
            $keyspace[$key] = (int) $value;
        }, explode(',', $keyspaceData));

        return $keyspace;
    }
}
