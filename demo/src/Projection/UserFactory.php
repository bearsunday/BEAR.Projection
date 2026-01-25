<?php

declare(strict_types=1);

namespace Demo\Projection\Projection;

final class UserFactory
{
    public function __invoke(string $id, string $name, string $email): UserProjection
    {
        return new UserProjection($id, $name, $email);
    }
}
