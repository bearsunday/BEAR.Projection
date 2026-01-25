<?php

declare(strict_types=1);

namespace BEAR\Projection\Exception;

use function implode;

final class ParameterMismatchException extends LogicException
{
    /** @param list<string> $missing */
    public function __construct(
        public readonly array $missing,
    ) {
        parent::__construct(implode(', ', $missing));
    }
}
