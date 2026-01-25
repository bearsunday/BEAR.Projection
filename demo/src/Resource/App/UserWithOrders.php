<?php

declare(strict_types=1);

namespace Demo\Projection\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class UserWithOrders extends ResourceObject
{
    #[Embed(src: 'query://self/user?id={id}', rel: 'user')]
    #[Embed(src: 'query://self/order?user_id={id}', rel: 'orders')]
    public function onGet(string $id): static
    {
        // Add to existing body (don't overwrite embedded resources)
        $this->body['id'] = $id;

        return $this;
    }
}
