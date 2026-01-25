<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake\Projection;

final class UserProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
