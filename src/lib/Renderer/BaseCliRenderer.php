<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Renderer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCliRenderer implements InstanceStatusRendererInterface
{
    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    private const UNIT = [
        0 => 'B',
        1 => 'kB',
        2 => 'MB',
        3 => 'GB',
        4 => 'TB',
    ];

    protected function formatBytes(float $bytes, $unitIndex = 0): string
    {
        if ($bytes >= 1024) {
            ++$unitIndex;

            return $this->formatBytes($bytes / 1024, $unitIndex);
        }

        return round($bytes, 2) . ' ' . self::UNIT[$unitIndex];
    }
}
