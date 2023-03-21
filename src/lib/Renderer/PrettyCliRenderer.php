<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Renderer;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\InstanceStatus;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Info\Memory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class PrettyCliRenderer extends BaseCliRenderer
{
    protected TranslatorInterface $translator;

    public function __construct(InputInterface $input, OutputInterface $output, TranslatorInterface $translator)
    {
        $this->translator = $translator;

        parent::__construct($input, $output);
    }

    public function render(InstanceStatus $instanceStatus): void
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->title($this->translator->trans('title'));
        $memory = $instanceStatus->getInfo()->getMemory();
        $stats = $instanceStatus->getInfo()->getStats();
        $nonEvictableKeys = $stats->getNumberOfAllKeys() - $stats->getNumberOfKeysWithExpiry();

        if (!$instanceStatus->isMinUptimeReached()) {
            $io->note(
                $this->translator->trans('note.uptime', [
                    '{uptime}' => $stats->getUptimeInDays(),
                    '{min_uptime}' => InstanceStatus::MIN_UPTIME,
                ])
            );
        }

        if (!$instanceStatus->isMaxMemoryPolicySupported()) {
            $io->error(
                $this->translator->trans(
                    'error.maxmemory_policy',
                    [
                    '{policy}' => $memory->getMaxMemoryPolicy(),
                    '{supported_policies}' => implode(', ', Memory::SUPPORTED_MAXMEMORY_POLICIES),
                    ]
                )
            );
        }

        if ($memory->getMaxMemory() === 0) {
            $io->warning($this->translator->trans('warning.maxmemory_not_set'));
        } elseif ($instanceStatus->getNonEvictableMemory() !== 0) {
            if ($instanceStatus->isNonEvictableMemoryThresholdReached()) {
                $io->warning(
                    $this->translator->trans('warning.non-evictable_memory_threshold', [
                        '{non-evictable_memory}' => $instanceStatus->getNonEvictableMemoryPercentage(),
                        '{non-evictable_memory_threshold}' => InstanceStatus::NON_EVICTABLE_THRESHOLD,
                    ])
                );
            }
        } else {
            if ($instanceStatus->isNonEvictableKeysNumberThresholdReached()) {
                $io->warning(
                    $this->translator->trans('warning.non-evictable_keys_threshold', [
                        '{non-evictable_keys}' => $nonEvictableKeys,
                        '{non-evictable_keys_threshold}' => InstanceStatus::NON_EVICTABLE_KEYS_NUMBER_THRESHOLD,
                    ])
                );
            }
        }

        $io->info(
            $this->translator->trans('info.memory', [
                '{max_memory}' => $this->formatBytes($memory->getMaxMemory()),
                '{used_memory}' => $this->formatBytes($memory->getUsedMemory()),
                '{non-evictable_memory}' => $instanceStatus->getNonEvictableMemory() !== 0 ? $this->formatBytes($instanceStatus->getNonEvictableMemory()) : 'N/A',
            ])
        );

        $io->info(
            $this->translator->trans('info.keys', [
                '{all_keys}' => $stats->getNumberOfAllKeys(),
                '{non-evictable_keys}' => $nonEvictableKeys,
                '{evicted_keys}' => $stats->getEvictedKeysNumber(),
            ])
        );

        if ($stats->getEvictedKeysNumber() === 0) {
            $io->note(
                $this->translator->trans('note.no_evicted_keys', [
                    '{uptime}' => $stats->getUptimeInDays(),
                ])
            );
        } else {
            if ($instanceStatus->isEvictionPercentagePerDayThresholdReached()) {
                $io->note(
                    $this->translator->trans('note.eviction_percentage_threshold', [
                        '{eviction_percentage}' => $instanceStatus->getEvictedKeysPerDayPercentage(),
                    ])
                );
            }
        }
    }
}
