<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\Annotation\ProjectionNamespace;
use BEAR\Projection\Annotation\SqlDir;
use BEAR\Projection\ProjectionAdapter;
use BEAR\Projection\ProjectionResourceInterface;
use BEAR\Projection\Resource\ProjectionResource;
use Ray\Di\AbstractModule;

final class ProjectionModule extends AbstractModule
{
    public function __construct(
        private readonly string $sqlDir,
        private readonly string $projectionNamespace,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith(SqlDir::class)->toInstance($this->sqlDir);
        $this->bind()->annotatedWith(ProjectionNamespace::class)->toInstance($this->projectionNamespace);

        $this->bind(ProjectionResourceInterface::class)->to(ProjectionResource::class);
        $this->bind(ProjectionAdapter::class);
    }
}
