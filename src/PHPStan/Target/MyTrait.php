<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Target;

/**
 * @ore-require-property value
 */
trait MyTrait
{
    public function toJson(): string
    {
        return json_encode($this->value);
    }
}
