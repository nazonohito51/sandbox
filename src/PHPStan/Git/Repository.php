<?php
declare(strict_types=1);

namespace Sandbox\PHPStan\Git;

use Cz\Git\GitException;
use Cz\Git\GitRepository;
use Webmozart\PathUtil\Path;

class Repository extends GitRepository
{
    public function __construct(string $repository)
    {
        parent::__construct($repository);
    }

    public function getAddedFiles(): array
    {
        try {
            $files = $this->extractFromCommand('git diff --diff-filter=A --name-only master...HEAD', function ($value) {
                return Path::join($this->getRepositoryPath(), trim($value));
            });

            if (!empty($files)) {
                return $files;
            }
        } catch (GitException $e) {
            // handle exceptions
        }

        return [];
    }
}
