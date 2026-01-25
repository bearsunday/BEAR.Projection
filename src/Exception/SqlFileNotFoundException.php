<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

use function sprintf;

final class SqlFileNotFoundException extends RuntimeException
{
    public function __construct(string $sqlFile)
    {
        parent::__construct(sprintf('SQL file not found: %s', $sqlFile));
    }
}
