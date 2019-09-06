<?php
namespace Sandbox\Domain\Entity;

use Sandbox\Domain\ValueObjects\Money;

class OrderDetail
{
    private Money $money;

    public function getAmount(): Money
    {
        return $this->money;
    }
}
