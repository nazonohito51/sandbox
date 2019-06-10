<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Target;


class SomeClass3
{
    /**
     * @return array
     * @deprecated
     */
    public static function someMethod1()
    {
        return [];
    }

    public function someMethod2()
    {
        return self::someMethod1();
    }
}