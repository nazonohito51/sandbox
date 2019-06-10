<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Target;


class SomeClass4
{
    public function someMethod1()
    {
        SomeClass3::someMethod1();
    }
}
