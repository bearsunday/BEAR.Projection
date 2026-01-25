<?php

declare(strict_types=1);

namespace BEAR\Projection\Projection;

/**
 * User Projection - CQRS読み取りモデル
 */
final class UserProjection
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
