<?php

namespace Northlab\FilamentThemeManager\Enum;

use Closure;
use Spatie\Enum\Enum;

class AssetCompilerEnum extends Enum
{
    protected static function values(): Closure
    {
        return function (string $name): string|int {
            return mb_strtolower($name);
        };
    }
}