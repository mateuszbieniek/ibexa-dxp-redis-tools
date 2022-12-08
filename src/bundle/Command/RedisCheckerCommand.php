<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisToolsBundle\Command;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Info;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RedisCheckerCommand extends BaseRedisCommand
{
    protected static $defaultName = 'ibexa:redis-check';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        $io = new SymfonyStyle($input, $output);
        $io->title("Ibexa's Redis Checker");

        $info = $this->redisGateway->getInfo();

        $keysWithoutExpiry = $this->redisGateway->getKeysWithoutExpiry();
        $nonEvictableMemory = $this->redisGateway->getMemoryUsedByKeys($keysWithoutExpiry);

        if (!$info->isMaxMemoryPolicySupported()) {
            $io->warning('maxmemory_policy is set to ' . $info->getMaxMemoryPolicy() . '. Please change to one of the following: ' . implode(', ', Info::SUPPORTED_MAXMEMORY_POLICIES));
        }

        $io->info(
            'Max memory: ' . $this->formatBytes($info->getMaxMemory()) . ' | ' .
            'Used memory: ' . $this->formatBytes($info->getUsedMemory()) . ' | ' .
            'Non-evictable memory: ' . $this->formatBytes($nonEvictableMemory)
        );

        if ($info->getMaxMemory() !== 0) {
            $nonEvictableMemoryPercentage = round($nonEvictableMemory / $info->getMaxMemory() * 100);
            if ($nonEvictableMemoryPercentage > self::NONEVICTABLE_THRESHOLD) {
                $io->warning('Non-evictable keys take ' . $nonEvictableMemoryPercentage . '% of your max memory. Consider increasing maxmemory setting.');
            } else {
                $io->success('Non-evictable keys take ' . $nonEvictableMemoryPercentage . '% of your max memory. No action needed.');
            }
        } else {
            $io->warning('Your redis instance has no maxmemory setting set');
        }

        $endTime = microtime(true);
        $io->info('Script took ' . round($endTime - $startTime) . ' seconds to complete');

        return Command::SUCCESS;
    }
}
