<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Type\CallableType;

class CallableSignatureRule implements Rule
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
    /**
     * @var FunctionCallParametersCheck
     */
    private $check;

    public function __construct(
        Broker $broker,
        Lexer $phpDocLexer,
        PhpDocParser $phpDocParser,
        FunctionCallParametersCheck $check
    ) {
        $this->broker = $broker;
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
        $this->check = $check;
    }

    public function getNodeType(): string
    {
        return Node\Expr\MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Expr\MethodCall) {
            return [];
        }

        // $scope からメソッド呼び出ししている変数の型を取得
        // $hoge->fugaMethod(); だったら $hoge の部分の解決
        $type = $scope->getType($node->var);

        // $type にはTypeインターフェースのオブジェクトが入っている
        // こいつからメソッド定義(MethodReflection)を取得、これは最終的にBrokerによるReflectionの取得が行われる
        $method = $type->getMethod($node->name->toString(), $scope);

        if (!$method instanceof PhpMethodReflection) {
            return [];
        }

        $errors = [];
        if (!empty($callableDeclarations = $this->getClosureDeclarationsFromPhpDoc($method))) {
            foreach ($callableDeclarations as $index => $callableDeclaration) {
                if (isset($node->args[$index])) {
                    if ($node->args[$index]->value instanceof Node\Expr\Closure) {
                        $errors = array_merge(
                            $errors,
                            $this->checkParamsOfClosure($node->args[$index]->value, $callableDeclaration)
                        );
                    }
                }
            }
        }

        // TODO: CallMethodRuleから持ってきた、ここらへん見ればちゃんとした引数チェックできそう
//        $messagesMethodName = $method->getDeclaringClass()->getDisplayName() . '::' . $method->getName() . '()';
//        $errors = $this->check->check(
//            ParametersAcceptorSelector::selectFromArgs(
//                $scope,
//                $node->args,
//                $method->getVariants()
//            ),
//            $scope,
//            $node,
//            [
//                'Method ' . $messagesMethodName . ' invoked with %d parameter, %d required.',
//                'Method ' . $messagesMethodName . ' invoked with %d parameters, %d required.',
//                'Method ' . $messagesMethodName . ' invoked with %d parameter, at least %d required.',
//                'Method ' . $messagesMethodName . ' invoked with %d parameters, at least %d required.',
//                'Method ' . $messagesMethodName . ' invoked with %d parameter, %d-%d required.',
//                'Method ' . $messagesMethodName . ' invoked with %d parameters, %d-%d required.',
//                'Parameter #%d %s of method ' . $messagesMethodName . ' expects %s, %s given.',
//                'Result of method ' . $messagesMethodName . ' (void) is used.',
//                'Parameter #%d %s of method ' . $messagesMethodName . ' is passed by reference, so it expects variables only.',
//            ]
//        );

        return [];
    }

    /**
     * @param PhpMethodReflection $method
     * @return array
     */
    public function getClosureDeclarationsFromPhpDoc(PhpMethodReflection $method): array
    {
        if (!$method->getDocComment()) {
            return [];
        }

        $callableDeclarations = [];

        $tokens = new TokenIterator($this->phpDocLexer->tokenize($method->getDocComment()));
        $phpDocNode = $this->phpDocParser->parse($tokens);
        foreach ($phpDocNode->getParamTagValues() as $index => $paramTagValue) {
            if (preg_match('/Closure\(([^\)]+)\):(.+)/', $paramTagValue->type->__toString(), $matches) === 1) {
                $params = explode(', ', $matches[1]);
                $return = $matches[2];
                $callableDeclarations[$index] = [
                    'params' => $params,
                    'return' => $return
                ];
            }
        }

        return $callableDeclarations;
    }

    private function checkParamsOfClosure(Node\Expr\Closure $closure, array $callableDeclarations): array
    {
        $errors = [];

        foreach ($closure->getParams() as $index => $param) {
            if ((string)$param->type !== $callableDeclarations['params'][$index]) {
                $errors[] = (string)$param->type . ' and ' . $callableDeclarations['params'][$index];
            }
        }

        if ((string)$closure->getReturnType() !== $callableDeclarations['return']) {
            $errors[] = 'return ' . (string)$closure->getReturnType() . ' and ' . $callableDeclarations['return'];
        }

        return $errors;
    }
}
