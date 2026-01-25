<?php

declare(strict_types=1);

namespace BEAR\Projection\Module;

use BEAR\Projection\ProjectionAdapter;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final readonly class ProjectionSchemeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ProjectionAdapter $projectionAdapter,
    ) {
    }

    #[Override]
    public function get(): SchemeCollectionInterface
    {
        $schemeCollection = new SchemeCollection();

        $schemeCollection
            ->scheme('query')
            ->host('self')
            ->toAdapter($this->projectionAdapter);

        return $schemeCollection;
    }
}
