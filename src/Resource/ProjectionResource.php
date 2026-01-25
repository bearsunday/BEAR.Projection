<?php

declare(strict_types=1);

namespace BEAR\Projection\Resource;

use BEAR\Projection\Annotation\ProjectionNamespace;
use BEAR\Projection\Annotation\SqlDir;
use BEAR\Projection\Exception\ParameterMismatchException;
use BEAR\Projection\Exception\ProjectionNotFoundException;
use BEAR\Projection\Exception\QueryExecuteException;
use BEAR\Projection\Exception\QueryPrepareException;
use BEAR\Projection\Exception\SqlFileNotFoundException;
use BEAR\Projection\ProjectionResourceInterface;
use BEAR\Projection\StringCase;
use BEAR\Resource\ResourceObject;
use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;
use Ray\Di\InjectorInterface;
use Throwable;

use function array_diff;
use function array_keys;
use function array_map;
use function array_values;
use function class_exists;
use function file_exists;
use function file_get_contents;
use function is_float;
use function is_int;
use function ltrim;
use function preg_match_all;
use function preg_replace;

use const MYSQLI_ASSOC;

final class ProjectionResource extends ResourceObject implements ProjectionResourceInterface
{
    public function __construct(
        private readonly mysqli $mysqli,
        private readonly InjectorInterface $injector,
        #[SqlDir]
        private readonly string $sqlDir,
        #[ProjectionNamespace]
        private readonly string $projectionNamespace,
    ) {
    }

    /**
     * Handle GET request for query resource
     *
     * Parameters are extracted from the URI query string by BEAR.Resource framework.
     * The projection name is extracted from $this->uri->path.
     *
     * @param array<string, mixed> $params Additional parameters (merged with URI query)
     */
    public function onGet(array $params = []): static
    {
        // Get projection name from URI path (set by Factory)
        $name = ltrim($this->uri->path, '/');

        // Merge URI query parameters with method parameters
        $allParams = [...$this->uri->query, ...$params];

        return $this->executeQuery($name, $allParams);
    }

    /** @param array<string, mixed> $params */
    private function executeQuery(string $name, array $params): static
    {
        // 1. Locate SQL file and Projection class
        $sqlFile = $this->sqlDir . '/' . $name . '.sql';
        if (! file_exists($sqlFile)) {
            throw new SqlFileNotFoundException($sqlFile);
        }

        /** @var class-string $factoryClass */
        $factoryClass = $this->projectionNamespace . StringCase::camel($name) . 'Factory';
        if (! class_exists($factoryClass)) {
            throw new ProjectionNotFoundException($factoryClass);
        }

        // 2. Load SQL and convert placeholders
        $sql = (string) file_get_contents($sqlFile);
        [$query, $boundParams] = $this->convertPlaceholders($sql, $params);

        // 3. Execute mysqli query
        try {
            $stmt = $this->mysqli->prepare($query);
        } catch (mysqli_sql_exception $e) {
            throw new QueryPrepareException($e->getMessage(), $query);
        }

        if ($stmt === false) {
            throw new QueryPrepareException($this->getMysqliError(), $query);
        }

        try {
            $this->bindParams($stmt, $boundParams);
            try {
                $executeResult = $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                throw new QueryExecuteException($e->getMessage());
            }

            if (! $executeResult) {
                throw new QueryExecuteException($this->getStmtError($stmt));
            }

            // 4. Fetch result and transform via Projection (Factory via DI)
            $result = $stmt->get_result();
            if ($result === false) {
                throw new QueryExecuteException($this->getStmtError($stmt));
            }

            $rows = $result->fetch_all(MYSQLI_ASSOC);
        } finally {
            $stmt->close();
        }

        /** @var callable(mixed...): object $factory */
        $factory = $this->injector->getInstance($factoryClass);
        $this->body = array_map(
            static fn (array $row): object => $factory(...array_values($row)),
            $rows,
        );

        return $this;
    }

    /**
     * Convert :name placeholders to ? and align parameter order
     *
     * @param array<string, mixed> $params
     *
     * @return array{0: string, 1: list<mixed>}
     */
    private function convertPlaceholders(string $sql, array $params): array
    {
        // Extract placeholder names
        preg_match_all('/:(\w+)/', $sql, $matches);
        /** @var list<non-empty-string> $placeholderNames */
        $placeholderNames = $matches[1];

        // Validate: ensure all placeholders have corresponding parameters
        $missing = array_diff($placeholderNames, array_keys($params));
        if ($missing !== []) {
            throw new ParameterMismatchException(array_values($missing));
        }

        $boundParams = array_map(
            /** @return mixed */
            static fn (string $name): mixed => $params[$name],
            $placeholderNames,
        );

        // Convert placeholders to ?
        $query = (string) preg_replace('/:(\w+)/', '?', $sql);

        return [$query, $boundParams];
    }

    /** @param list<mixed> $params */
    private function bindParams(mysqli_stmt $stmt, array $params): void
    {
        if ($params === []) {
            return;
        }

        $types = '';
        /** @var mixed $value */
        foreach ($params as $value) {
            $types .= $this->getParamType($value);
        }

        // PHP 8.0+ properly handles spread operator for by-reference parameters
        /** @psalm-suppress MixedArgument */
        $stmt->bind_param($types, ...$params);
    }

    private function getParamType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'i',
            is_float($value) => 'd',
            default => 's',
        };
    }

    private function getMysqliError(): string
    {
        try {
            return $this->mysqli->error;
        } catch (Throwable) {
            return 'Unknown error';
        }
    }

    private function getStmtError(mysqli_stmt $stmt): string
    {
        try {
            return $stmt->error;
        } catch (Throwable) {
            return 'Unknown error';
        }
    }
}
