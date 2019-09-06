<?php
namespace Sandbox\Domain\ValueObjects;

class Money
{
    /**
     * @var int
     */
    private $value;

    const UNIT = 'jpy';

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function add(Money $money)
    {
        return new self($this->value + $money->value);
    }

    public static function zero()
    {
        return new self(0);
    }
}
