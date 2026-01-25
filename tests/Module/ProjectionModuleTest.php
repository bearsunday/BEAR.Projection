<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\ProjectionAdapter;
use BEAR\Projection\ProjectionResourceInterface;
use BEAR\Projection\Resource\ProjectionResource;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\SchemeCollectionInterface;
use mysqli;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

#[CoversClass(ProjectionModule::class)]
final class ProjectionModuleTest extends TestCase
{
    public const SQL_DIR = '/tmp/projection';
    public const PROJECTION_NAMESPACE = 'App\\Projection\\';

    public function testBindProjectionResourceInterface(): void
    {
        $injector = $this->createInjector();
        $instance = $injector->getInstance(ProjectionResourceInterface::class);

        $this->assertInstanceOf(ProjectionResource::class, $instance);
    }

    public function testBindProjectionAdapter(): void
    {
        $injector = $this->createInjector();
        $instance = $injector->getInstance(ProjectionAdapter::class);

        $this->assertInstanceOf(ProjectionAdapter::class, $instance);
    }

    public function testBindSchemeCollection(): void
    {
        $injector = $this->createInjector();
        $instance = $injector->getInstance(SchemeCollectionInterface::class);

        $this->assertInstanceOf(SchemeCollectionInterface::class, $instance);
    }

    private function createInjector(): Injector
    {
        $mysqli = $this->createStub(mysqli::class);

        return new Injector(new class ($mysqli) extends AbstractModule {
            public function __construct(
                private readonly mysqli $mysqli,
            ) {
                parent::__construct();
            }

            protected function configure(): void
            {
                $this->bind(mysqli::class)->toInstance($this->mysqli);
                $this->bind(SchemeCollectionInterface::class)->to(SchemeCollection::class);
                $this->install(new ProjectionModule(
                    ProjectionModuleTest::SQL_DIR,
                    ProjectionModuleTest::PROJECTION_NAMESPACE,
                ));
            }
        });
    }
}
