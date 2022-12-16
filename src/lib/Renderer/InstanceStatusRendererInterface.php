<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Renderer;

use MateuszBieniek\IbexaDxpRedisTools\ValueObject\InstanceStatus;

interface InstanceStatusRendererInterface
{
    public function render(InstanceStatus $instanceStatus): void;
}
