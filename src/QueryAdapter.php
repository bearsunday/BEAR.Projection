<?php

declare(strict_types=1);

namespace BEAR\Projection;

use BEAR\Resource\AbstractUri;
use BEAR\Resource\AdapterInterface;
use BEAR\Resource\ResourceObject;
use Override;

use function ltrim;

final readonly class QueryAdapter implements AdapterInterface
{
    public function __construct(
        private QueryResourceInterface $queryResource,
    ) {
    }

    #[Override]
    public function get(AbstractUri $uri): ResourceObject
    {
        // パスから先頭スラッシュを除去してProjection名を取得
        $name = ltrim($uri->path, '/');

        // QueryResourceのonGetを呼び出し
        return $this->queryResource->onGet($name, $uri->query);
    }
}
