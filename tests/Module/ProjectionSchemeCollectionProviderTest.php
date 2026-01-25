<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\Fake\FakeInjector;
use BEAR\Projection\Fake\FakeProjectionResource;
use BEAR\Projection\ProjectionAdapter;
use BEAR\Projection\Resource\ProjectionResource;
use BEAR\Resource\SchemeCollectionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectionSchemeCollectionProvider::class)]
final class ProjectionSchemeCollectionProviderTest extends TestCase
{
    public function testGetRegistersQueryScheme(): void
    {
        $injector = new FakeInjector();
        $injector->setInstance(ProjectionResource::class, new FakeProjectionResource());

        $projectionAdapter = new ProjectionAdapter($injector);
        $provider = new ProjectionSchemeCollectionProvider($projectionAdapter);

        $result = $provider->get();

        $this->assertInstanceOf(SchemeCollectionInterface::class, $result);
    }
}
