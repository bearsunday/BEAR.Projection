<?php

declare(strict_types=1);

namespace Demo\Projection\Module;

use BEAR\Projection\ProjectionAdapter;
use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\HttpAdapter;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final readonly class AppSchemeCollectionProvider implements ProviderInterface
{
    public function __construct(
        #[AppName]
        private string $appName,
        private InjectorInterface $injector,
        private ProjectionAdapter $projectionAdapter,
    ) {
    }

    #[Override]
    public function get(): SchemeCollection
    {
        $schemeCollection = new SchemeCollection();

        // Standard schemes (page, app, http)
        $pageAdapter = new AppAdapter($this->injector, $this->appName);
        $appAdapter = new AppAdapter($this->injector, $this->appName);
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);
        $schemeCollection->scheme('http')->host('self')->toAdapter(new HttpAdapter($this->injector));

        // Query scheme for projections
        $schemeCollection->scheme('query')->host('self')->toAdapter($this->projectionAdapter);

        return $schemeCollection;
    }
}
