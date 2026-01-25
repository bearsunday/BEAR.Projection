<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake\Projection;

final class UserFactory
{
    public function __invoke(string $id, string $name): UserProjection
    {
        return new UserProjection($id, $name);
    }
}
