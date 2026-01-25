<?php

declare(strict_types=1);

namespace BEAR\Projection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringCaseTest extends TestCase
{
    /** @return array<string, array{0: string, 1: string}> */
    public static function camelCaseDataProvider(): array
    {
        return [
            'simple' => ['user', 'User'],
            'two_words' => ['user_profile', 'UserProfile'],
            'three_words' => ['admin_user_list', 'AdminUserList'],
            'already_camel' => ['User', 'User'],
            'single_letter' => ['a', 'A'],
        ];
    }

    #[DataProvider('camelCaseDataProvider')]
    public function testCamel(string $input, string $expected): void
    {
        $this->assertSame($expected, StringCase::camel($input));
    }
}
