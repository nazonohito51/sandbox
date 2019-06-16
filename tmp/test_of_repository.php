<?php

use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$repository = new \Sandbox\PHPStan\Git\Repository(__DIR__ . '/../');

var_dump($repository->getMergeBase());
var_dump($repository->getAddedFiles());
$current = $repository->getFileContent(__DIR__ . '/../src/PHPStan/Git/Repository.php');
$before = $repository->getFileContent(__DIR__ . '/../src/PHPStan/Git/Repository.php', $repository->getMergeBase());



$currentClassMethods = [];
$beforeClassMethods = [];



$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
try {
    $currentAst = $parser->parse($current);
    $beforeAst = $parser->parse($before);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    return;
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
$traverser->traverse($beforeAst);
$beforeClassMethods = $visitor->classMethods;
$visitor->classMethods = [];
$traverser->traverse($currentAst);
$currentClassMethods = $visitor->classMethods;

foreach ($currentClassMethods as $currentClassMethod) {
    $newMethod = true;
    foreach ($beforeClassMethods as $beforeClassMethod) {
        if ($currentClassMethod->name->toLowerString() === $beforeClassMethod->name->toLowerString()) {
            $newMethod = false;
            break;
        }
    }

    if ($newMethod) {
        // require return params
        var_dump($currentClassMethod);
    }
}


$test = 'test';
