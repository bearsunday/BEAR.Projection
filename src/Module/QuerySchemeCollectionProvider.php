<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\QueryAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final readonly class QuerySchemeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private SchemeCollectionInterface $schemeCollection,
        private QueryAdapter $queryAdapter,
    ) {
    }

    #[Override]
    public function get(): SchemeCollectionInterface
    {
        $this->schemeCollection
            ->scheme('query')
            ->host('self')
            ->toAdapter($this->queryAdapter);

        return $this->schemeCollection;
    }
}
