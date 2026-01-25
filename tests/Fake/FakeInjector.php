<?php

declare(strict_types=1);

namespace BEAR\Projection\Fake;

use Override;
use Ray\Di\InjectorInterface;

final class FakeInjector implements InjectorInterface
{
    /** @var array<class-string, object> */
    private array $instances = [];

    /** @param class-string $interface */
    public function setInstance(string $interface, object $instance): void
    {
        $this->instances[$interface] = $instance;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $interface
     *
     * @return T
     */
    #[Override]
    public function getInstance($interface, $name = '')
    {
        /** @var T */
        return $this->instances[$interface];
    }
}
