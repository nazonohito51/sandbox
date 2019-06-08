<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Sandbox\PHPStan\Git\Repository;

class ReturnTypeDeclarationsInNewClass implements Rule
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (in_array($scope->getFile(), $this->repository->getAddedFiles())) {
            foreach ($node->stmts as $stmt) {
                $errors = [];
                if ($stmt instanceof Node\Stmt\ClassMethod) {
                    // TODO: git diffの対象をメソッド単位まで落とし込みたい
                    if (is_null($stmt->getReturnType())) {
                        $errors[] = 'Class method in new class must have return type declaration.';
                    }
                }

                return $errors;
            }
        }

        return [];
    }
}
