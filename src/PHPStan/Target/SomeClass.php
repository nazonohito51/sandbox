<?php

namespace Sandbox\PHPStan\Target;

class SomeClass
{
    private $someProperty;

    public function someMethod()
    {
        return function ($someParam) {
            return $someParam;
        };
    }
}
