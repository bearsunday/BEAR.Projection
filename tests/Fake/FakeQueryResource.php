<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake;

use BEAR\Projection\QueryResourceInterface;
use BEAR\Resource\ResourceObject;
use Override;

final class FakeQueryResource extends ResourceObject implements QueryResourceInterface
{
    public string $calledName = '';

    /** @var array<string, mixed> */
    public array $calledParams = [];

    /** @param array<string, mixed> $params */
    #[Override]
    public function onGet(string $name, array $params = []): static
    {
        $this->calledName = $name;
        $this->calledParams = $params;
        $this->body = ['fake' => 'data'];

        return $this;
    }
}
