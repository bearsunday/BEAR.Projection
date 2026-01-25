<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Projection\Resource\ProjectionResource;
use BEAR\Resource\AbstractUri;
use BEAR\Resource\AdapterInterface;
use BEAR\Resource\ResourceObject;
use Override;
use Ray\Di\InjectorInterface;

final readonly class ProjectionAdapter implements AdapterInterface
{
    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    #[Override]
    public function get(AbstractUri $uri): ResourceObject
    {
        return $this->injector->getInstance(ProjectionResource::class);
    }
}
