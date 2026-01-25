<?php

declare(strict_types=1);

namespace BEAR\Projection\Resource;

use BEAR\Projection\Exception\ParameterMismatchException;
use BEAR\Projection\Exception\ProjectionNotFoundException;
use BEAR\Projection\Exception\QueryPrepareException;
use BEAR\Projection\Exception\SqlFileNotFoundException;
use BEAR\Projection\Fake\Projection\UserFactory;
use BEAR\Projection\Fake\Projection\UserProfileFactory;
use BEAR\Projection\Fake\Projection\UserProfileProjection;
use BEAR\Projection\Fake\Projection\UserProjection;
use BEAR\Resource\Uri;
use mysqli;
use PHPUnit\Framework\TestCase;
use Ray\Di\InjectorInterface;

use function dirname;
use function file_put_contents;
use function getenv;
use function http_build_query;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

final class ProjectionResourceTest extends TestCase
{
    private const PROJECTION_NAMESPACE = 'BEAR\\Projection\\Fake\\Projection\\';
    private string $sqlDir;
    private mysqli $mysqli;

    protected function setUp(): void
    {
        $this->sqlDir = dirname(__DIR__) . '/Fake/Projection';
        $this->mysqli = new mysqli(
            getenv('MYSQL_HOST') ?: '127.0.0.1',
            getenv('MYSQL_USER') ?: 'test_user',
            getenv('MYSQL_PASSWORD') ?: 'test_pass',
            getenv('MYSQL_DATABASE') ?: 'test_db',
            (int) (getenv('MYSQL_PORT') ?: 3306),
        );

        $this->mysqli->query('DROP TABLE IF EXISTS users');
        $this->mysqli->query('
            CREATE TABLE users (
                id VARCHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL
            )
        ');

        $this->mysqli->query('DROP TABLE IF EXISTS user_profiles');
        $this->mysqli->query('
            CREATE TABLE user_profiles (
                id VARCHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL
            )
        ');
    }

    protected function tearDown(): void
    {
        $this->mysqli->close();
    }

    private function createResource(string|null $sqlDir = null): ProjectionResource
    {
        $injector = $this->createStub(InjectorInterface::class);

        return new ProjectionResource(
            $this->mysqli,
            $injector,
            $sqlDir ?? $this->sqlDir,
            self::PROJECTION_NAMESPACE,
        );
    }

    /** @param array<string, string|int> $query */
    private function setUri(ProjectionResource $resource, string $path, array $query = []): void
    {
        $queryString = $query ? '?' . http_build_query($query) : '';
        $resource->uri = new Uri('query://self' . $path . $queryString);
    }

    public function testOnGetUser(): void
    {
        $this->mysqli->query("INSERT INTO users (id, name) VALUES (1, 'John')");

        $injector = $this->createStub(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserFactory::class)
            ->willReturn(new UserFactory());

        $resource = new ProjectionResource($this->mysqli, $injector, $this->sqlDir, self::PROJECTION_NAMESPACE);
        $this->setUri($resource, '/user', ['id' => '1']);
        $result = $resource->onGet();

        $this->assertIsArray($result->body);
        $this->assertCount(1, $result->body);
        $this->assertInstanceOf(UserProjection::class, $result->body[0]);
        $this->assertSame('1', $result->body[0]->id);
        $this->assertSame('John', $result->body[0]->name);
    }

    public function testOnGetMultipleRows(): void
    {
        $this->mysqli->query("INSERT INTO users (id, name) VALUES (1, 'John'), (2, 'Jane')");

        $injector = $this->createStub(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserFactory::class)
            ->willReturn(new UserFactory());

        $resource = new ProjectionResource($this->mysqli, $injector, $this->sqlDir, self::PROJECTION_NAMESPACE);
        $this->setUri($resource, '/user', ['id' => '1']);
        $result = $resource->onGet();

        $this->assertIsArray($result->body);
        $this->assertCount(1, $result->body);
        $this->assertInstanceOf(UserProjection::class, $result->body[0]);
        $this->assertSame('1', $result->body[0]->id);
    }

    public function testOnGetSnakeCaseName(): void
    {
        $this->mysqli->query("INSERT INTO user_profiles (id, name, email) VALUES (1, 'John', 'john@example.com')");

        $injector = $this->createStub(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserProfileFactory::class)
            ->willReturn(new UserProfileFactory());

        $resource = new ProjectionResource($this->mysqli, $injector, $this->sqlDir, self::PROJECTION_NAMESPACE);
        $this->setUri($resource, '/user_profile', ['id' => '1']);
        $result = $resource->onGet();

        $this->assertIsArray($result->body);
        $this->assertCount(1, $result->body);
        $this->assertInstanceOf(UserProfileProjection::class, $result->body[0]);
        $this->assertSame('1', $result->body[0]->id);
        $this->assertSame('John', $result->body[0]->name);
        $this->assertSame('john@example.com', $result->body[0]->email);
    }

    public function testSqlFileNotFound(): void
    {
        $resource = $this->createResource();
        $this->setUri($resource, '/nonexistent');

        $this->expectException(SqlFileNotFoundException::class);

        $resource->onGet();
    }

    public function testProjectionNotFound(): void
    {
        $tempDir = sys_get_temp_dir() . '/bear-projection-test';
        @mkdir($tempDir);
        file_put_contents($tempDir . '/unknown.sql', 'SELECT 1');

        $resource = $this->createResource($tempDir);
        $this->setUri($resource, '/unknown');

        $this->expectException(ProjectionNotFoundException::class);

        try {
            $resource->onGet();
        } finally {
            @unlink($tempDir . '/unknown.sql');
            @rmdir($tempDir);
        }
    }

    public function testOnGetEmptyResult(): void
    {
        $injector = $this->createStub(InjectorInterface::class);
        $injector->method('getInstance')
            ->with(UserFactory::class)
            ->willReturn(new UserFactory());

        $resource = new ProjectionResource($this->mysqli, $injector, $this->sqlDir, self::PROJECTION_NAMESPACE);
        $this->setUri($resource, '/user', ['id' => '999']);
        $result = $resource->onGet();

        $this->assertIsArray($result->body);
        $this->assertCount(0, $result->body);
    }

    public function testOnGetMissingParameter(): void
    {
        $resource = $this->createResource();
        $this->setUri($resource, '/user');

        $this->expectException(ParameterMismatchException::class);
        $this->expectExceptionMessage('id');

        $resource->onGet();
    }

    public function testOnGetPrepareFails(): void
    {
        $tempDir = sys_get_temp_dir() . '/bear-projection-test-prepare';
        @mkdir($tempDir);
        file_put_contents($tempDir . '/user.sql', 'SELEC * FROM nonexistent WHERE id = :id');

        $resource = $this->createResource($tempDir);
        $this->setUri($resource, '/user', ['id' => '1']);

        $this->expectException(QueryPrepareException::class);

        try {
            $resource->onGet();
        } finally {
            @unlink($tempDir . '/user.sql');
            @rmdir($tempDir);
        }
    }

    public function testOnGetExecuteWithInvalidTable(): void
    {
        $tempDir = sys_get_temp_dir() . '/bear-projection-test-execute';
        @mkdir($tempDir);
        file_put_contents($tempDir . '/user.sql', 'SELECT * FROM nonexistent_table WHERE id = :id');

        $resource = $this->createResource($tempDir);
        $this->setUri($resource, '/user', ['id' => '1']);

        $this->expectException(QueryPrepareException::class);

        try {
            $resource->onGet();
        } finally {
            @unlink($tempDir . '/user.sql');
            @rmdir($tempDir);
        }
    }
}
