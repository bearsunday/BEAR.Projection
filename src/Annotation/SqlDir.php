<?php

declare(strict_types=1);

namespace BEAR\Projection\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class SqlDir
{
}
