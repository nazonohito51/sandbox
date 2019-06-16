<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class ExpressionEmulatorRule implements Rule
{
    public function getNodeType(): string
    {
        return NodeAbstract::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [];
    }
}
