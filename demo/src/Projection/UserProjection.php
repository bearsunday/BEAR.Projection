<?php

declare(strict_types=1);

namespace Demo\Projection\Projection;

final class UserProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
