<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

final class SqlFileNotFoundException extends RuntimeException
{
    public function __construct(
        public readonly string $sqlFile,
    ) {
        parent::__construct($sqlFile);
    }
}
