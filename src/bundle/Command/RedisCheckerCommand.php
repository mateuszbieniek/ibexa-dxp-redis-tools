<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisToolsBundle\Command;

use MateuszBieniek\IbexaDxpRedisTools\Redis\Gateway\Factory\RedisGatewayFactoryInterface;
use MateuszBieniek\IbexaDxpRedisTools\Renderer\JsonCliOutputCliRenderer;
use MateuszBieniek\IbexaDxpRedisTools\Renderer\PrettyCliOutputCliRenderer;
use MateuszBieniek\IbexaDxpRedisTools\ValueObject\InstanceStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RedisCheckerCommand extends BaseRedisCommand
{
    protected static $defaultName = 'ibexa:redis-check';

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(RedisGatewayFactoryInterface $redisGatewayFactory, TranslatorInterface $translator)
    {
        parent::__construct($redisGatewayFactory);

        $this->translator = $translator;
    }

    protected function configure(): void
    {
        $this->addOption(
            'calculateMemory',
            'm',
            InputOption::VALUE_NONE,
            'Will calculate non-evictable memory. Can take long time on big databases.'
        );

        $this->addOption(
            'format',
            'f',
            InputOption::VALUE_REQUIRED,
            'How the command output should be formatted? Accepted values: text, json.',
            'text'
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $calculateMemory = $input->getOption('calculateMemory');
        $format = $input->getOption('format');
        switch ($format) {
            case 'text':
                $renderer = new PrettyCliOutputCliRenderer($input, $output, $this->translator);
                break;
            case 'json':
                $renderer = new JsonCliOutputCliRenderer($input, $output);
                break;
            default:
                throw new InvalidOptionException('--format option only accepts: text, json');
        }

        $nonEvictableMemory = 0;
        if ($calculateMemory) {
            $keysWithoutExpiry = $this->redisGateway->getKeysWithoutExpiry();
            $nonEvictableMemory = $this->redisGateway->getMemoryUsedByKeys($keysWithoutExpiry);
        }
        $instanceStatus = new InstanceStatus($this->redisGateway->getInfo(), $nonEvictableMemory);
        $renderer->render($instanceStatus);

        return Command::SUCCESS;
    }
}
