<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Git;
require_once __DIR__ . '/../../../vendor/autoload.php';
use Cz\Git\GitException;
use Cz\Git\GitRepository;

class Repository extends GitRepository
{
    public function __construct(string $repository)
    {
        parent::__construct($repository);
    }

    public function getChangedFiles(): array
    {
        try {
            return $this->extractFromCommand('git diff --diff-filter=AM --name-only master...HEAD', function ($value) {
                return trim($value);
            });
        } catch (GitException $e) {
            // handle exceptions
        }

        return [];
    }
}

$repo = new Repository(__DIR__ . '/../../../');
var_dump($repo->getChangedFiles());
