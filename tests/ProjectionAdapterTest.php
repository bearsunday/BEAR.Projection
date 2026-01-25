<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Projection\Fake\FakeInjector;
use BEAR\Projection\Fake\FakeProjectionResource;
use BEAR\Projection\Resource\ProjectionResource;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;

final class ProjectionAdapterTest extends TestCase
{
    public function testGetReturnsNewProjectionResourceInstance(): void
    {
        $projectionResource = new FakeProjectionResource();
        $injector = new FakeInjector();
        $injector->setInstance(ProjectionResource::class, $projectionResource);

        $adapter = new ProjectionAdapter($injector);

        $uri = new Uri('query://self/user?id=1');
        $result = $adapter->get($uri);

        $this->assertSame($projectionResource, $result);
    }
}
