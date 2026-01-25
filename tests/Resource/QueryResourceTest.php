<?php

declare(strict_types=1);

namespace BEAR\Projection\Resource;

use BEAR\Projection\Exception\ProjectionNotFoundException;
use BEAR\Projection\Exception\SqlFileNotFoundException;
use BEAR\Projection\Projection\UserFactory;
use BEAR\Projection\Projection\UserProjection;
use mysqli;
use mysqli_result;
use mysqli_stmt;
use PHPUnit\Framework\TestCase;
use Ray\Di\InjectorInterface;

use function dirname;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

use const MYSQLI_ASSOC;

final class QueryResourceTest extends TestCase
{
    private const PROJECTION_NAMESPACE = 'BEAR\\Projection\\Projection\\';
    private string $projectionDir;

    protected function setUp(): void
    {
        $this->projectionDir = dirname(__DIR__, 2) . '/src/Projection';
    }

    public function testOnGetUser(): void
    {
        // mysqli モック設定
        $result = $this->createMock(mysqli_result::class);
        $result->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([
                ['id' => '1', 'name' => 'John'],
            ]);

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $mysqli = $this->createMock(mysqli::class);
        $mysqli->method('prepare')
            ->with('SELECT id, name FROM users WHERE id = ?' . "\n")
            ->willReturn($stmt);

        // Injector モック設定
        $injector = $this->createMock(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserFactory::class)
            ->willReturn(new UserFactory());

        $resource = new QueryResource($mysqli, $injector, $this->projectionDir, self::PROJECTION_NAMESPACE);
        $result = $resource->onGet('user', ['id' => '1']);

        $this->assertIsArray($result->body);
        $this->assertCount(1, $result->body);
        $this->assertInstanceOf(UserProjection::class, $result->body[0]);
        $this->assertSame('1', $result->body[0]->id);
        $this->assertSame('John', $result->body[0]->name);
    }

    public function testOnGetMultipleRows(): void
    {
        $result = $this->createMock(mysqli_result::class);
        $result->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([
                ['id' => '1', 'name' => 'John'],
                ['id' => '2', 'name' => 'Jane'],
            ]);

        $stmt = $this->createMock(mysqli_stmt::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $mysqli = $this->createMock(mysqli::class);
        $mysqli->method('prepare')->willReturn($stmt);

        $injector = $this->createMock(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserFactory::class)
            ->willReturn(new UserFactory());

        $resource = new QueryResource($mysqli, $injector, $this->projectionDir, self::PROJECTION_NAMESPACE);
        $result = $resource->onGet('user', ['id' => '1']);

        $this->assertIsArray($result->body);
        $this->assertCount(2, $result->body);
        $this->assertInstanceOf(UserProjection::class, $result->body[0]);
        $this->assertInstanceOf(UserProjection::class, $result->body[1]);
        $this->assertSame('1', $result->body[0]->id);
        $this->assertSame('2', $result->body[1]->id);
    }

    public function testSqlFileNotFound(): void
    {
        $mysqli = $this->createMock(mysqli::class);
        $injector = $this->createMock(InjectorInterface::class);

        $resource = new QueryResource($mysqli, $injector, $this->projectionDir, self::PROJECTION_NAMESPACE);

        $this->expectException(SqlFileNotFoundException::class);
        $this->expectExceptionMessage('SQL file not found:');

        $resource->onGet('nonexistent', []);
    }

    public function testProjectionNotFound(): void
    {
        $mysqli = $this->createMock(mysqli::class);
        $injector = $this->createMock(InjectorInterface::class);

        // Create a temporary SQL file for a projection that doesn't exist
        $tempDir = sys_get_temp_dir() . '/bear-projection-test';
        @mkdir($tempDir);
        file_put_contents($tempDir . '/unknown.sql', 'SELECT 1');

        $resource = new QueryResource($mysqli, $injector, $tempDir, self::PROJECTION_NAMESPACE);

        $this->expectException(ProjectionNotFoundException::class);
        $this->expectExceptionMessage('Projection class not found:');

        try {
            $resource->onGet('unknown', []);
        } finally {
            @unlink($tempDir . '/unknown.sql');
            @rmdir($tempDir);
        }
    }
}
