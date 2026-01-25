<?php

declare(strict_types=1);

namespace Demo\Projection\Resource\App;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource,
    ) {
    }

    public function onGet(string $id): static
    {
        // Direct query:// scheme access
        $queryResult = $this->resource->get('query://self/user', ['id' => $id]);

        // Extract body from the query resource
        $this->body = $queryResult->body;

        return $this;
    }
}
