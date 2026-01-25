<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

final class QueryExecuteException extends RuntimeException
{
    public function __construct(string $error)
    {
        parent::__construct($error);
    }
}
