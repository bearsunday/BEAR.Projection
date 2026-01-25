<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

use function sprintf;

final class ProjectionNotFoundException extends LogicException
{
    public function __construct(string $projectionClass)
    {
        parent::__construct(sprintf('Projection class not found: %s', $projectionClass));
    }
}
