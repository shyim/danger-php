<?php declare(strict_types=1);

namespace Danger\Struct\Local;

use Danger\Exception\CouldNotGetFileContentException;
use Danger\Struct\CommentCollection;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\File;
use Danger\Struct\FileCollection;
use Danger\Struct\PullRequest;
use Symfony\Component\Process\Process;

class LocalPullRequest extends PullRequest
{
    /**
     * @var CommitCollection<Commit>|null
     */
    private ?CommitCollection $commits = null;

    /**
     * @var FileCollection<File>|null
     */
    private ?FileCollection $files = null;

    public function __construct(private string $repo, private string $local, private string $target)
    {
        $this->projectIdentifier = $this->local;
        $this->id = $this->local;
        $this->body = '';

        $commits = $this->getCommits();

        if ($commits->count() > 0) {
            $firstCommit = $commits->first();
            \assert($firstCommit !== null);
            $this->title = $firstCommit->message;
            $this->createdAt = $firstCommit->createdAt;
            $this->updatedAt = $firstCommit->createdAt;
        } else {
            $this->title = 'empty';
            $this->createdAt = new \DateTime();
            $this->updatedAt = new \DateTime();
        }
    }

    public function getCommits(): CommitCollection
    {
        if ($this->commits !== null) {
            return $this->commits;
        }

        $process = new Process([
            'git',
            'log',
            '--pretty=format:{%n  "commit": "%H",%n  "abbreviated_commit": "%h",%n  "tree": "%T",%n  "abbreviated_tree": "%t",%n  "parent": "%P",%n  "abbreviated_parent": "%p",%n  "refs": "%D",%n  "encoding": "%e",%n  "subject": "%s",%n  "sanitized_subject_line": "%f",%n  "body": "%b",%n  "commit_notes": "%N",%n  "verification_flag": "%G?",%n  "signer": "%GS",%n  "signer_key": "%GK",%n  "author": {%n    "name": "%aN",%n    "email": "%aE",%n    "date": "%aD"%n  },%n  "commiter": {%n    "name": "%cN",%n    "email": "%cE",%n    "date": "%cD"%n  }%n},',
            $this->target . '..' . $this->local,
        ], $this->repo);

        $process->mustRun();

        $commits = new CommitCollection();

        /** @var array{commit: string, author: array{name: string, email: string, date: string}, subject: string}[] $gitOutput */
        $gitOutput = json_decode('[' . mb_substr($process->getOutput(), 0, -1) . ']', true);

        foreach ($gitOutput as $commit) {
            $commitObj = new Commit();
            $commitObj->sha = $commit['commit'];
            $commitObj->author = $commit['author']['name'];
            $commitObj->authorEmail = $commit['author']['email'];
            $commitObj->message = $commit['subject'];
            $commitObj->createdAt = new \DateTime($commit['author']['date']);

            $commits->add($commitObj);
        }

        return $this->commits = $commits;
    }

    public function getFile(string $fileName): File
    {
        return new LocalFile($this->repo . '/' . $fileName);
    }

    public function getFiles(): FileCollection
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $process = new Process([
            'git',
            'diff',
            $this->target . '..' . $this->local,
            '--name-status',
        ], $this->repo);

        $process->mustRun();

        $files = new FileCollection();

        foreach (explode(\PHP_EOL, $process->getOutput()) as $line) {
            if ($line === '') {
                continue;
            }

            $status = $line[0];
            $file = trim(mb_substr($line, 1));

            $element = new LocalFile($this->repo . '/' . $file);
            $element->name = $file;
            $element->additions = 0;
            $element->changes = 0;
            $element->deletions = 0;

            if ($status === 'A') {
                $element->status = File::STATUS_ADDED;
            } elseif ($status === 'M') {
                $element->status = File::STATUS_MODIFIED;
            } else {
                $element->status = File::STATUS_REMOVED;
            }

            $files->set($element->name, $element);
        }

        // Get numstat for additions/deletions
        $numstatProcess = new Process([
            'git',
            'diff',
            $this->target . '..' . $this->local,
            '--numstat',
        ], $this->repo);

        $numstatProcess->mustRun();

        foreach (explode(\PHP_EOL, $numstatProcess->getOutput()) as $line) {
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $line, 3);
            if (count($parts) !== 3) {
                continue;
            }

            [$additions, $deletions, $filename] = $parts;
            $file = $files->get($filename);

            if ($file !== null) {
                $file->additions = $additions === '-' ? 0 : (int) $additions;
                $file->deletions = $deletions === '-' ? 0 : (int) $deletions;
                $file->changes = $file->additions + $file->deletions;
            }
        }

        // Get patch for each file
        $patchProcess = new Process([
            'git',
            'diff',
            $this->target . '..' . $this->local,
        ], $this->repo);

        $patchProcess->mustRun();

        $fullDiff = $patchProcess->getOutput();

        // Parse the full diff to extract patches for individual files
        $currentFile = null;
        $currentPatch = '';

        foreach (explode(\PHP_EOL, $fullDiff) as $line) {
            if (str_starts_with($line, 'diff --git ')) {
                // Save previous file's patch
                if ($currentFile !== null && $currentPatch !== '') {
                    $file = $files->get($currentFile);
                    if ($file !== null) {
                        $file->patch = $currentPatch;
                    }
                }

                // Extract filename from "diff --git a/filename b/filename"
                if (preg_match('/^diff --git a\/(.*) b\//', $line, $matches)) {
                    $currentFile = $matches[1];
                    $currentPatch = $line . \PHP_EOL;
                }
            } elseif ($currentFile !== null) {
                $currentPatch .= $line . \PHP_EOL;
            }
        }

        // Save last file's patch
        if ($currentFile !== null && $currentPatch !== '') {
            $file = $files->get($currentFile);
            if ($file !== null) {
                $file->patch = rtrim($currentPatch);
            }
        }

        return $this->files = $files;
    }

    public function getComments(): CommentCollection
    {
        return new CommentCollection();
    }

    public function getFileContent(string $path): string
    {
        $file = $this->repo . '/' . $path;

        if (!file_exists($file)) {
            throw new CouldNotGetFileContentException($path);
        }

        return (string) file_get_contents($file);
    }
}
