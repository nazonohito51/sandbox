<?php

use PhpParser\NodeDumper;
use PhpParser\ParserFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

$ast = $parser->parse(file_get_contents(__DIR__ . '/token_get_all_target.php'));

$dumper = new NodeDumper;
echo $dumper->dump($ast) . "\n";
