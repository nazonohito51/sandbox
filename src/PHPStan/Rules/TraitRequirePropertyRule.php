<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Rules\Rule;

class TraitRequirePropertyRule implements Rule
{
    /**
     * @var Broker
     */
    private $broker;
    /**
     * @var Lexer
     */
    private $phpDocLexer;
    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    public function __construct(Broker $broker, Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->broker = $broker;
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }

    public function getNodeType(): string
    {
        return Node\Stmt\TraitUse::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$scope->isInClass()) {
            return [];
        }

        $errors = [];
        foreach ($node->traits as $trait) {
            foreach ($this->getRequiredProperties($trait->toString()) as $requiredProperty) {
                if (!$scope->getClassReflection()->hasProperty($requiredProperty)) {
                    $errors[] = "Class using {$trait->toString()} must have {$requiredProperty}.";
                }
            }
        }

        return $errors;
    }

    private function getRequiredProperties(string $traitName): array
    {
        $traitReflection = $this->broker->getClass($traitName);

        $requiredProperties = [];
        if ($docComment = $traitReflection->getNativeReflection()->getDocComment()) {
            $tokens = new TokenIterator($this->phpDocLexer->tokenize($docComment));
            $phpDocNode = $this->phpDocParser->parse($tokens);
            foreach ($phpDocNode->getTagsByName('@ore-require-property') as $phpDocTagNode) {
                $requiredProperties[] = $phpDocTagNode->value->__toString();
            }
        }

        return $requiredProperties;
    }
}
