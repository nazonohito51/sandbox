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

        $class = $scope->getClassReflection();

        $errors = [];
        foreach ($node->traits as $trait) {
            $traitReflection = $this->broker->getClass($trait->toString());

            if ($docComment = $traitReflection->getNativeReflection()->getDocComment()) {
                $tokens = new TokenIterator($this->phpDocLexer->tokenize($docComment));
                $phpDocNode = $this->phpDocParser->parse($tokens);
                foreach ($phpDocNode->getTagsByName('@ore-require-property') as $phpDocTagNode) {
                    if (!$class->hasProperty($phpDocTagNode->value->__toString())) {
                        $errors[] = "Class using {$trait->toString()} must have {$phpDocTagNode->value->__toString()}.";
                    }
                }
            }
        }

        return $errors;
    }
}
