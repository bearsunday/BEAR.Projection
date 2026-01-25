<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake;

use BEAR\Projection\ProjectionResourceInterface;
use BEAR\Resource\ResourceObject;
use Override;

use function ltrim;

final class FakeProjectionResource extends ResourceObject implements ProjectionResourceInterface
{
    public string $calledName = '';

    /** @var array<string, mixed> */
    public array $calledParams = [];

    /** @param array<string, mixed> $params */
    #[Override]
    public function onGet(array $params = []): static
    {
        $this->calledName = ltrim($this->uri->path ?? '', '/');
        $this->calledParams = [...($this->uri->query ?? []), ...$params];
        $this->body = ['fake' => 'data'];

        return $this;
    }
}
