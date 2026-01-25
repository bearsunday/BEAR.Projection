<?php

declare(strict_types=1);

namespace BEAR\Projection;

use function lcfirst;
use function str_replace;
use function ucwords;

final class StringCase
{
    /**
     * Convert snake_case to camelCase
     *
     * @param string $snakeName snake_case string (e.g., "user_profile")
     *
     * @return string CamelCase string (e.g., "UserProfile")
     */
    public static function camel(string $snakeName): string
    {
        return str_replace('_', '', ucwords($snakeName, '_'));
    }
}
