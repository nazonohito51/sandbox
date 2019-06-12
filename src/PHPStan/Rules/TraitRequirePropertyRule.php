<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
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

        $traitReflection = $this->broker->getClass($node->traits->toString());

        return [];
    }
}
