<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisToolsBundle\Command;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\Factory\RedisGatewayFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseRedisCommand extends Command
{
    protected const UNIT = [
        0 => 'B',
        1 => 'kB',
        2 => 'MB',
        3 => 'GB',
        4 => 'TB',
    ];

    protected const MAXMEMORY_POLICY = [
        'volatile-lru',
        'volatile-lfu',
        'volatile-ttl',
    ];

    protected const NONEVICTABLE_THRESHOLD = 50;

    /** @var \MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\Factory\RedisGatewayFactoryInterface */
    private $redisGatewayFactory;

    /** @var \MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\RedisGatewayInterface */
    protected $redisGateway;

    public function __construct(RedisGatewayFactoryInterface $redisGatewayFactory)
    {
        $this->redisGatewayFactory = $redisGatewayFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('host', InputArgument::OPTIONAL, 'Redis instance host', 'localhost');
        $this
            ->addArgument('port', InputArgument::OPTIONAL, 'Redis instance port', 6379);
    }

    /**
     * @throws \RedisException
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $host = $input->getArgument('host');
        $port = (int) $input->getArgument('port');

        $this->redisGateway = $this->redisGatewayFactory->getGateway($host, $port);
    }

    protected function formatBytes(float $bytes, $unitIndex = 0): string
    {
        if ($bytes >= 1024) {
            ++$unitIndex;

            return $this->formatBytes($bytes / 1024, $unitIndex);
        }

        return round($bytes, 2) . ' ' . self::UNIT[$unitIndex];
    }
}
