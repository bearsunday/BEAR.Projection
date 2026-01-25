<?php

declare(strict_types=1);

namespace BEAR\Projection\Projection;

/**
 * User Factory - SQLの行データをUserProjectionに変換するファクトリ
 *
 * Ray.MediaQueryのFetchInjectionFactoryパターン:
 * - DIで依存を注入可能
 * - __invokeで行データを受け取りProjectionを生成
 */
final class UserFactory
{
    /**
     * SQL行データを受け取り、UserProjectionを生成
     *
     * @param string $id   SQL列: id
     * @param string $name SQL列: name
     */
    public function __invoke(string $id, string $name): UserProjection
    {
        return new UserProjection($id, $name);
    }
}
