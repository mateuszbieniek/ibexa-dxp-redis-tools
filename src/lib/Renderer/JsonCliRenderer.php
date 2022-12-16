<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Renderer;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\InstanceStatus;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonCliRenderer extends BaseCliRenderer
{
    public function render(InstanceStatus $instanceStatus): void
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->output->writeln($serializer->serialize($instanceStatus, 'json'));
    }
}
