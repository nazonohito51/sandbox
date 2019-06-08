<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Target;

class SomeClass1
{
    public function someMethod1()
    {
        return;
    }

    public function someMethod2(): array
    {
        return [];
    }
}