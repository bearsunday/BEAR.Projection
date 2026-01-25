<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\QueryAdapter;
use BEAR\Projection\QueryResourceInterface;
use BEAR\Projection\Resource\QueryResource;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\AbstractModule;

final class QueryModule extends AbstractModule
{
    public function __construct(
        private readonly string $projectionDir,
        private readonly string $projectionNamespace = 'BEAR\\Projection\\Projection\\',
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    #[Override]
    protected function configure(): void
    {
        $this->bind()->annotatedWith('projection_dir')->toInstance($this->projectionDir);
        $this->bind()->annotatedWith('projection_namespace')->toInstance($this->projectionNamespace);

        // QueryResourceInterfaceをQueryResourceにバインド
        $this->bind(QueryResourceInterface::class)->to(QueryResource::class);

        // SchemeCollectionへのQueryAdapter登録はProviderで行う
        $this->bind(QueryAdapter::class);
        $this->bind(SchemeCollectionInterface::class)
            ->toProvider(QuerySchemeCollectionProvider::class);
    }
}
