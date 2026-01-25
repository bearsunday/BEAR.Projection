<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake\Projection;

final class UserProfileFactory
{
    public function __invoke(string $id, string $name, string $email): UserProfileProjection
    {
        return new UserProfileProjection($id, $name, $email);
    }
}
