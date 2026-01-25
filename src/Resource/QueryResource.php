<?php

declare(strict_types=1);

namespace BEAR\Projection\Resource;

use BEAR\Projection\Exception\ProjectionNotFoundException;
use BEAR\Projection\Exception\SqlFileNotFoundException;
use BEAR\Projection\QueryResourceInterface;
use BEAR\Projection\StringCase;
use BEAR\Resource\ResourceObject;
use mysqli;
use mysqli_result;
use mysqli_stmt;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\InjectorInterface;

use function array_map;
use function array_values;
use function assert;
use function class_exists;
use function count;
use function file_exists;
use function file_get_contents;
use function preg_match_all;
use function preg_replace;
use function str_repeat;

use const MYSQLI_ASSOC;

final class QueryResource extends ResourceObject implements QueryResourceInterface
{
    public function __construct(
        private readonly mysqli $mysqli,
        private readonly InjectorInterface $injector,
        #[Named('projection_dir')] private readonly string $projectionDir,
        #[Named('projection_namespace')] private readonly string $projectionNamespace,
    ) {
    }

    /** @param array<string, mixed> $params */
    #[Override]
    public function onGet(string $name, array $params = []): static
    {
        // 1. SQLファイルとProjectionクラスを特定
        $sqlFile = $this->projectionDir . '/' . $name . '.sql';
        if (! file_exists($sqlFile)) {
            throw new SqlFileNotFoundException($sqlFile);
        }

        /** @var class-string $factoryClass */
        $factoryClass = $this->projectionNamespace . StringCase::camel($name) . 'Factory';
        if (! class_exists($factoryClass)) {
            throw new ProjectionNotFoundException($factoryClass);
        }

        // 2. SQL読み込み・プレースホルダ変換
        $sql = (string) file_get_contents($sqlFile);
        [$query, $boundParams] = $this->convertPlaceholders($sql, $params);

        // 3. mysqli非同期クエリ実行
        $stmt = $this->mysqli->prepare($query);
        assert($stmt instanceof mysqli_stmt);
        $this->bindParams($stmt, $boundParams);
        $stmt->execute();

        // 4. 結果取得・Projection変換 (DIでFactory取得)
        $result = $stmt->get_result();
        assert($result instanceof mysqli_result);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        /** @var callable(mixed...): object $factory */
        $factory = $this->injector->getInstance($factoryClass);
        $this->body = array_map(
            static fn (array $row): object => $factory(...array_values($row)),
            $rows,
        );

        return $this;
    }

    /**
     * :name 形式を ? に変換し、パラメータ順序を整列
     *
     * @param array<string, mixed> $params
     *
     * @return array{0: string, 1: list<mixed>}
     */
    private function convertPlaceholders(string $sql, array $params): array
    {
        // プレースホルダ名を抽出
        preg_match_all('/:(\w+)/', $sql, $matches);
        /** @var list<non-empty-string> $placeholderNames */
        $placeholderNames = $matches[1];

        $boundParams = array_map(
            /** @return mixed */
            static fn (string $name): mixed => $params[$name] ?? null,
            $placeholderNames,
        );

        // プレースホルダを ? に変換
        $query = (string) preg_replace('/:(\w+)/', '?', $sql);

        return [$query, $boundParams];
    }

    /** @param list<mixed> $params */
    private function bindParams(mysqli_stmt $stmt, array $params): void
    {
        if ($params === []) {
            return;
        }

        $types = str_repeat('s', count($params)); // MVP: 全てstring
        $stmt->bind_param($types, ...$params);
    }
}
