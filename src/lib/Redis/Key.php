<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace MateuszBieniek\IbexaDxpRedisTools\Redis;

class Key
{
    /** @var string */
    private $name;

    /** @var int */
    private $ttl;

    /** @var string|string[]|null */
    private $value;

    /**
     * @param string $name
     * @param int $ttl
     * @param string|string[]|null $value
     */
    public function __construct(string $name, int $ttl, $value = null)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @return string|string[]|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
