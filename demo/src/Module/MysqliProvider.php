<?php

declare(strict_types=1);

namespace Demo\Projection\Module;

use mysqli;
use Ray\Di\ProviderInterface;

/**
 * @implements ProviderInterface<mysqli>
 */
final class MysqliProvider implements ProviderInterface
{
    public function get(): mysqli
    {
        $mysqli = new mysqli('127.0.0.1', 'demo_user', 'demo_pass', 'demo_db', 3307);
        if ($mysqli->connect_error) {
            throw new \RuntimeException('Connect Error: ' . $mysqli->connect_error);
        }

        return $mysqli;
    }
}
