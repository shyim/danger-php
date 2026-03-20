<?php
declare(strict_types=1);

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

        $diffRange = $this->target . '..' . $this->local;

        $nameStatusProcess = new Process([
            'git', 'diff', $diffRange, '--name-status',
        ], $this->repo);

        $numstatProcess = new Process([
            'git', 'diff', $diffRange, '--numstat',
        ], $this->repo);

        $patchProcess = new Process([
            'git', 'diff', $diffRange,
        ], $this->repo);

        $nameStatusProcess->mustRun();
        $numstatProcess->mustRun();
        $patchProcess->mustRun();

        $numstats = $this->parseNumstat($numstatProcess->getOutput());
        $patches = $this->parsePatch($patchProcess->getOutput());

        $files = new FileCollection();

        foreach (explode(\PHP_EOL, $nameStatusProcess->getOutput()) as $line) {
            if ($line === '') {
                continue;
            }

            $status = $line[0];
            $file = mb_trim(mb_substr($line, 1));

            $element = new LocalFile($this->repo . '/' . $file);
            $element->name = $file;
            $element->additions = $numstats[$file]['additions'] ?? 0;
            $element->deletions = $numstats[$file]['deletions'] ?? 0;
            $element->changes = $element->additions + $element->deletions;
            $element->patch = $patches[$file] ?? '';

            if ($status === 'A') {
                $element->status = File::STATUS_ADDED;
            } elseif ($status === 'M') {
                $element->status = File::STATUS_MODIFIED;
            } else {
                $element->status = File::STATUS_REMOVED;
            }

            $files->set($element->name, $element);
        }

        return $this->files = $files;
    }

    /**
     * @return array<string, array{additions: int, deletions: int}>
     */
    private function parseNumstat(string $output): array
    {
        $result = [];

        foreach (explode(\PHP_EOL, $output) as $line) {
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\t/', $line, 3);
            if ($parts === false || \count($parts) < 3) {
                continue;
            }

            $result[$parts[2]] = [
                'additions' => $parts[0] === '-' ? 0 : (int) $parts[0],
                'deletions' => $parts[1] === '-' ? 0 : (int) $parts[1],
            ];
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function parsePatch(string $output): array
    {
        $result = [];
        $currentFile = null;
        $currentPatch = '';
        $fallbackFile = null;

        foreach (explode(\PHP_EOL, $output) as $line) {
            if (str_starts_with($line, 'diff --git ')) {
                if ($currentFile !== null) {
                    $result[$currentFile] = $currentPatch;
                }

                $currentFile = null;
                $currentPatch = '';
                $fallbackFile = null;

                continue;
            }

            if ($currentFile === null && str_starts_with($line, '--- a/')) {
                $fallbackFile = mb_substr($line, 6);

                continue;
            }

            if ($currentFile === null && str_starts_with($line, '+++ b/')) {
                $currentFile = mb_substr($line, 6);

                continue;
            }

            if ($currentFile === null && $line === '+++ /dev/null') {
                $currentFile = $fallbackFile;

                continue;
            }

            if ($currentFile === null && str_starts_with($line, '--- ')) {
                continue;
            }

            if ($currentFile !== null) {
                $currentPatch .= ($currentPatch !== '' ? \PHP_EOL : '') . $line;
            }
        }

        if ($currentFile !== null) {
            $result[$currentFile] = $currentPatch;
        }

        return $result;
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
