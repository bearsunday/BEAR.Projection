<?php

declare(strict_types=1);

namespace Demo\Projection\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Projection\Module\ProjectionModule;
use BEAR\Resource\Module\EmbedResourceModule;
use BEAR\Resource\SchemeCollectionInterface;
use Koriym\EnvJson\EnvJson;
use mysqli;
use Ray\Di\Scope;

use function dirname;

final class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new EnvJson())->load(dirname(__DIR__, 2));

        // mysqli binding
        $this->bind(mysqli::class)->toProvider(MysqliProvider::class)->in(Scope::SINGLETON);

        // ProjectionModule installation
        $sqlDir = dirname(__DIR__, 2) . '/var/sql';
        $this->install(new ProjectionModule($sqlDir, 'Demo\\Projection\\Projection\\'));

        // Custom SchemeCollectionProvider that includes query:// scheme
        $this->bind(SchemeCollectionInterface::class)
            ->toProvider(AppSchemeCollectionProvider::class);

        // Install EmbedResourceModule for #[Embed] support
        $this->install(new EmbedResourceModule());

        $this->install(new PackageModule());
    }
}
