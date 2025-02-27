<?php

namespace Northlab\FilamentThemeManager\Enum;

use Spatie\Enum\Enum;
use Closure;

/**
 * @method static self PENDING()
 * @method static self PROCESSING()
 * @method static self FAILED()
 * @method static self SUCCESSED()
 * @method static self RETRYING()
 */

class DeploymentStatusEnum extends Enum
{
    protected static function values(): Closure
    {
        return function (string $name): string|int {
            return mb_strtolower($name);
        };
    }
}