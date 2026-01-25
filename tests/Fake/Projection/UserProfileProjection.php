<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake\Projection;

final class UserProfileProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
