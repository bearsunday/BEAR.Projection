<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Resource\ResourceObject;

interface ProjectionResourceInterface
{
    /**
     * Handle GET request for query resource
     *
     * The projection name is extracted from $this->uri->path.
     * Parameters are extracted from the URI query string.
     *
     * @param array<string, mixed> $params Additional parameters (merged with URI query)
     */
    public function onGet(array $params = []): ResourceObject;
}
