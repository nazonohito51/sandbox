<?php

namespace Sandbox;

class SomeClass
{
    private $someProperty = 1;

    public function someMethod(string $someParam): string
    {
        $str = 'test';
        return $someParam . $str;
    }
}

//foreach (token_get_all(file_get_contents(__FILE__)) as $token) {
//    if (is_array($token)) {
//        echo "Line {$token[2]}: ", token_name($token[0]), " ('{$token[1]}')", PHP_EOL;
//    } else {
//        echo "{$token}\n";
//    }
//}
