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
        if (is_null($mergeBase = $this->getMergeBase())) {
            throw new \RuntimeException();
        }

        try {
            $files = $this->extractFromCommand("git diff --diff-filter=A --name-only {$mergeBase}...HEAD", function ($value) {
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

    public function getModifiedFiles(): array
    {
        if (is_null($mergeBase = $this->getMergeBase())) {
            throw new \RuntimeException();
        }

        try {
            $files = $this->extractFromCommand("git diff --diff-filter=M --name-only {$mergeBase}...HEAD", function ($value) {
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

    public function getFileContent(string $file, string $commitHash = null): string
    {
        try {
            $commitHash = $commitHash ?? 'HEAD';
            $file = Path::makeRelative($file, $this->getRepositoryPath());
            $content = $this->extractFromCommand("git show {$commitHash}:{$file}", function ($value) {
                return $value;
            });

            return implode("\n", $content);
        } catch (GitException $e) {
            // handle exceptions
        }

        throw new \RuntimeException();
    }

    public function getMergeBase(): string
    {
        try {
            $commitHash = $this->extractFromCommand('git show-branch --merge-base test HEAD', function ($value) {
                return trim($value);
            });

            if (isset($commitHash[0])) {
                return $commitHash[0];
            }
        } catch (GitException $e) {
            // handle exceptions
        }

        throw new \RuntimeException();
    }
}
