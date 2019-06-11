<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
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
        if ($this->repository->isAddedOrModifiedFile($scope->getFile())) {
            return [];
        }

        $newClassMethods = $this->repository->isAddedFile($scope->getFile()) ?
            $this->getNewClassMethodsFromAddedFile($scope->getFile()) :
            $this->getNewClassMethodsFromModifiedFile($scope->getFile());

        $errors = [];
        foreach ($newClassMethods as $newClassMethod) {
            if (is_null($newClassMethod->getReturnType())) {
                $errors[] = 'Class method in new class must have return type declaration.';
            }
        }

        return $errors;
    }

    /**
     * @param string $file
     * @return Node\Stmt\ClassMethod[]
     */
    private function getNewClassMethodsFromAddedFile(string $file): array
    {
        return $this->getClassMethods($this->repository->getFileContent($file));
    }

    private function getNewClassMethodsFromModifiedFile(string $file): array
    {
        $beforeClassMethods = $this->getClassMethods($this->repository->getFileContent($file));
        $currentClassMethods = $this->getClassMethods($this->repository->getFileContent($file, $this->repository->getMergeBase()));

        $newClassMethods = [];
        foreach ($currentClassMethods as $currentClassMethod) {
            $isNewClass = true;
            foreach ($beforeClassMethods as $beforeClassMethod) {
                if ($currentClassMethod->name->toLowerString() === $beforeClassMethod->name->toLowerString()) {
                    $isNewClass = false;
                    break;
                }
            }

            if ($isNewClass) {
                $newClassMethods[] = $currentClassMethod;
            }
        }

        return $newClassMethods;
    }

    /**
     * @param string $content
     * @return Node\Stmt\ClassMethod[]
     */
    private function getClassMethods(string $content): array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($content);
        } catch (Error $error) {
            throw new \RuntimeException("Parse error: {$error->getMessage()}");
        }

        $traverser = new NodeTraverser();
        $visitor = new class extends NodeVisitorAbstract {
            public $classMethods = [];
            public function enterNode(Node $node) {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    $this->classMethods[] = $node;
                }
            }
        };
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->classMethods;
    }
}
