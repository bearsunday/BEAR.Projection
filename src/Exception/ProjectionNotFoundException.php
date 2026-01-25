<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

final class ProjectionNotFoundException extends LogicException
{
    public function __construct(
        public readonly string $factoryClass,
    ) {
        parent::__construct($factoryClass);
    }
}
