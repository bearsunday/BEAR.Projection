<?php

declare(strict_types=1);

namespace Demo\Projection\Projection;

final class OrderProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $total,
    ) {
    }
}
