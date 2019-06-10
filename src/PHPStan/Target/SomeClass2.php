<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Target;


class SomeClass2
{
//    /**
//     * @param callable(string, int):bool $procedure
//     * @return bool
//     */
//    public function someMethod1(callable $procedure): bool
//    {
//        $str = 'string';
//        $int = 1;
//        return $procedure($str, $int);
//    }

    /**
     * @param callable(Hoge, Fuga):Piyo $procedure
     * @return Piyo
     */
    public function someMethod1(callable $procedure): Piyo
    {
        $hoge = new Hoge();
        $fuga = new Fuga();
        return $procedure($hoge, $fuga);
    }

    public function someMethod2(): void
    {
        $this->someMethod1(function (Hoge $hoge, Fuga $fuga): Piyo {
            return new Piyo();
        });
//        $this->someMethod1(function (Hoge $hoge, int $fuga): Piyo {
//            return new Piyo();
//        });
    }
}
