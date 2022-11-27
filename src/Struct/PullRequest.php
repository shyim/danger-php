<?php
declare(strict_types=1);

namespace Danger\Struct;

abstract class PullRequest
{
    public string $id;

    public string $projectIdentifier;

    public string $title;

    public string $body;

    public \DateTimeInterface $createdAt;

    public \DateTimeInterface $updatedAt;

    /**
     * @var string[]
     */
    public array $labels = [];

    /**
     * @var string[]
     */
    public array $assignees = [];

    /**
     * @var string[]
     */
    public array $reviewers = [];

    /**
     * @var array<string, array{'id': string, 'committed_date': string, 'message': 'string', 'author_name': string, 'author_email': string}|string>
     */
    public array $rawCommits = [];

    /**
     * @var array{'changes': array{'new_path': string, 'diff'?: string, 'new_file': bool, 'deleted_file': bool}[]}
     */
    public array $rawFiles = ['changes' => []];

    /**
     * @return CommitCollection<Commit>
     */
    abstract public function getCommits(): CommitCollection;

    /**
     * @return FileCollection<File>
     */
    abstract public function getFiles(): FileCollection;

    /**
     * Get a file from the pull request head. Don't need to be a changed file.
     */
    abstract public function getFileContent(string $path): string;

    /**
     * @return CommentCollection<Comment>
     */
    abstract public function getComments(): CommentCollection;
}
