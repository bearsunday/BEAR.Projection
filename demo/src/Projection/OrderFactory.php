<?php

declare(strict_types=1);

namespace Demo\Projection\Projection;

final class OrderFactory
{
    public function __invoke(string $id, string $user_id, string $total): OrderProjection
    {
        return new OrderProjection($id, $user_id, $total);
    }
}
