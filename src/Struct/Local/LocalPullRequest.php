<?php declare(strict_types=1);

namespace Danger\Struct\Local;

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
            } else {
                $element->status = File::STATUS_REMOVED;
            }

            $files->set($element->name, $element);
        }

        return $this->files = $files;
    }

    public function getComments(): CommentCollection
    {
        return new CommentCollection();
    }
}
