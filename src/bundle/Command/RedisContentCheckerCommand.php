<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RedisContentCheckerCommand extends BaseRedisCommand
{
    protected static $defaultName = 'ibexa:redis-content-check';

    protected function configure(): void
    {
        $this->addOption(
            'saveToFile',
            's',
            InputOption::VALUE_REQUIRED,
            'Path where the command\'s output should be saved.'
        );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $orphanedMembers = [];
        foreach ($this->redisGateway->getKeysWithoutExpiry() as $keyWithoutExpiry) {
            $members = $this->redisGateway->getMembers($keyWithoutExpiry);
            /** @var \MateuszBieniek\IbexaDxpRedisTools\ValueObject\Redis\Key $key */
            foreach ($members as $key) {
                if (!$this->redisGateway->keyExists($key)) {
                    $orphanedMembers[$keyWithoutExpiry->getName()][] = $key->getName();
                }
            }
        }

        if ($input->getOption('saveToFile')) {
            $filesystem = new Filesystem();
            $filesystem->appendToFile(
                $input->getOption('saveToFile'),
                json_encode(['orphanedMembers' => $orphanedMembers])
            );

            $output->writeln('Done!');
        } else {
            $this->render($output, $orphanedMembers);
            foreach ($orphanedMembers as $orphanedMember => $removedKeys) {
                $output->writeln($orphanedMember);
                foreach ($removedKeys as $removedKey) {
                    $output->writeln(' - ' . $removedKey);
                }
                $output->writeln('');
            }
        }

        return Command::SUCCESS;
    }

    private function render(OutputInterface $output, array $data): void
    {
        $output->writeln('### Orphaned Members ###');
        foreach ($data as $orphanedMember => $removedKeys) {
            $output->writeln($orphanedMember);
            foreach ($removedKeys as $removedKey) {
                $output->writeln(' - ' . $removedKey);
            }
            $output->writeln('');
        }
    }
}
