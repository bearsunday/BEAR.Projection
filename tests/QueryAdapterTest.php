<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Projection\Fake\FakeQueryResource;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;

final class QueryAdapterTest extends TestCase
{
    public function testGetReturnsQueryResource(): void
    {
        $queryResource = new FakeQueryResource();
        $adapter = new QueryAdapter($queryResource);

        $uri = new Uri('query://self/user?id=1');
        $result = $adapter->get($uri);

        $this->assertSame($queryResource, $result);
        $this->assertSame('user', $queryResource->calledName);
        $this->assertSame(['id' => '1'], $queryResource->calledParams);
    }

    public function testGetWithMultipleParams(): void
    {
        $queryResource = new FakeQueryResource();
        $adapter = new QueryAdapter($queryResource);

        $uri = new Uri('query://self/order?user_id=1&status=active');
        $result = $adapter->get($uri);

        $this->assertSame($queryResource, $result);
        $this->assertSame('order', $queryResource->calledName);
        $this->assertSame(['user_id' => '1', 'status' => 'active'], $queryResource->calledParams);
    }

    public function testGetWithNoParams(): void
    {
        $queryResource = new FakeQueryResource();
        $adapter = new QueryAdapter($queryResource);

        $uri = new Uri('query://self/users');
        $result = $adapter->get($uri);

        $this->assertSame($queryResource, $result);
        $this->assertSame('users', $queryResource->calledName);
        $this->assertSame([], $queryResource->calledParams);
    }

    public function testGetWithNestedPath(): void
    {
        $queryResource = new FakeQueryResource();
        $adapter = new QueryAdapter($queryResource);

        $uri = new Uri('query://self/admin/user?id=1');
        $result = $adapter->get($uri);

        $this->assertSame($queryResource, $result);
        $this->assertSame('admin/user', $queryResource->calledName);
        $this->assertSame(['id' => '1'], $queryResource->calledParams);
    }
}
