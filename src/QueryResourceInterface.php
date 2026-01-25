<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Resource\ResourceObject;

interface QueryResourceInterface
{
    /** @param array<string, mixed> $params */
    public function onGet(string $name, array $params = []): ResourceObject;
}
