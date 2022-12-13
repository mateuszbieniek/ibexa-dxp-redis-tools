<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisToolsBundle\Command;

use MateuszBieniek\IbexaDxpRedisTools\Redis\ValueObject\Info\Memory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RedisCheckerCommand extends BaseRedisCommand
{
    private const MIN_UPTIME = 14;

    protected static $defaultName = 'ibexa:redis-check';

    protected function configure(): void
    {
        $this->addOption(
            'calculateMemory',
            'm',
            InputOption::VALUE_NONE,
            'Will calculate non-evictable memory. Can take long time on big databases.'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $calculateMemory = $input->getOption('calculateMemory');
        $startTime = microtime(true);

        $io = new SymfonyStyle($input, $output);
        $io->title("Ibexa's Redis Checker");

        $info = $this->redisGateway->getInfo();
        $infos = [];
        $warnings = [];
        $errors = [];

        $maxMemory = $info->getMemory()->getMaxMemory();
        $numberOfAllKeys = $info->getStats()->getNumberOfAllKeys();
        $evictableKeysNumber = $info->getStats()->getNumberOfKeysWithExpiry();
        $nonEvictableMemory = 0;
        if ($calculateMemory) {
            $keysWithoutExpiry = $this->redisGateway->getKeysWithoutExpiry();
            $nonEvictableMemory = $this->redisGateway->getMemoryUsedByKeys($keysWithoutExpiry);
        }

        if ($nonEvictableMemory !== 0 && $maxMemory !== 0) {
            $nonEvictableMemoryPercentage = round($nonEvictableMemory / $maxMemory * 100);
            if ($nonEvictableMemoryPercentage > self::NONEVICTABLE_THRESHOLD) {
                $warnings[] = 'Non-evictable keys take ' . $nonEvictableMemoryPercentage . '% of your max memory. Consider increasing maxmemory setting.';
            } else {
                $infos[] = 'Non-evictable keys take ' . $nonEvictableMemoryPercentage . '% of your max memory. No action needed.';
            }
        }

        $uptime = $info->getStats()->getUptimeInDays();
        if ($uptime < self::MIN_UPTIME) {
            $warnings[] = sprintf(
                'Redis is running for less than %d days (%d). Information here can be less accurate due to that.',
                self::MIN_UPTIME,
                $uptime
            );
        }

        if (!$info->getMemory()->isMaxMemoryPolicySupported()) {
            $errors[] = sprintf(
                'maxmemory_policy is set to %s. It is required to change to one of the following: %s',
                $info->getMemory()->getMaxMemoryPolicy(),
                implode(', ', Memory::SUPPORTED_MAXMEMORY_POLICIES)
            );
        }

        $infos[] = 'Max memory: ' . $this->formatBytes($maxMemory);
        $infos[] = 'Used memory: ' . $this->formatBytes($info->getMemory()->getUsedMemory());
        !$calculateMemory ?: $infos[] = 'Non-evictable memory: ' . $this->formatBytes($nonEvictableMemory);
        $infos[] = 'All keys number: ' . $numberOfAllKeys;
        $infos[] = 'Non-evictable keys number: ' . ($numberOfAllKeys - $evictableKeysNumber);

        if ($maxMemory == 0) {
            $warnings[] = 'Your redis instance has no maxmemory setting set.';
        }

        if ($evictableKeysNumber !== 0 && !$calculateMemory) {
            $evictableKeysNumberPercentage = round($evictableKeysNumber / $numberOfAllKeys * 100);
            $message = sprintf('Evictable keys are only %d%% of number of your all keys.', $evictableKeysNumberPercentage);
            if ($evictableKeysNumberPercentage < self::NONEVICTABLE_THRESHOLD) {
                $message .= 'Probably you should check non-evictable memory.';
                $warnings[] = $message;
            } else {
                $message .= 'No action needed.';
                $infos[] = $message;
            }
        } else {
            $infos[] = sprintf(
                'Your Redis instance is running for %d %s and never reached maxmemory limit (no key was evicted). Maybe you can use this memory elsewhere?',
                $uptime,
                $uptime === 1 ? 'day' : 'days'
            );
        }

        foreach ($errors as $error) {
            $io->error($error);
        }

        foreach ($warnings as $warning) {
            $io->warning(($warning));
        }

        foreach ($infos as $info) {
            $io->info($info);
        }

        $endTime = microtime(true);
        $io->success('Script took ' . round($endTime - $startTime) . ' seconds to complete');

        return Command::SUCCESS;
    }
}
