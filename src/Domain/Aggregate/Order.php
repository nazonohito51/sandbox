<?php
namespace Sandbox\Domain\Aggregate;

use InvalidArgumentException;
use Sandbox\Aggregate\OrderHeader;
use Sandbox\Domain\Entity\OrderDetail;
use Sandbox\Domain\ValueObjects\Money;

class Order
{
    private OrderHeader $header;
    /**
     * @var OrderDetail[]
     */
    private array $details;

    public function __construct(OrderHeader $header, array $details)
    {
        if (count($details) === 0) {
            throw new InvalidArgumentException();
        }

        $this->header = $header;
        $this->details = $details;
    }

    public function getTotalAmount(): Money
    {
        $totalAmount = Money::zero();
        foreach ($this->details as $order) {
            $totalAmount = $totalAmount->add($order->getAmount());
        }

        return $totalAmount;
    }
}
