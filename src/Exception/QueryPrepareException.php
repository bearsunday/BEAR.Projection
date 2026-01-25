<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

final class QueryPrepareException extends RuntimeException
{
    public function __construct(
        string $error,
        public readonly string $query,
    ) {
        parent::__construct($error);
    }
}
