<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class TestClassMustExtendsTestCase implements Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return [];
        }

        if ($node->namespacedName instanceof Node\Name && $node->namespacedName->slice(0, 2)->toString() !== 'Tests\\Unit') {
            return [];
        } elseif (!is_null($node->extends) && $node->extends->toString() === 'Tests\\TestCase') {
            return [];
        }

        return ['Test class must extends Tests\\TestCase'];
    }
}
