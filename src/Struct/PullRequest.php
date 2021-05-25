<?php
declare(strict_types=1);

namespace Danger\Struct;

abstract class PullRequest
{
    public string $id;

    public string $projectIdentifier;

    public string $title;

    public string $body;

    public \DateTime $createdAt;

    public \DateTime $updatedAt;

    /**
     * @var string[]
     */
    public array $labels = [];

    /**
     * @var string[]
     */
    public array $assignees;

    /**
     * @var string[]
     */
    public array $reviewers;

    /**
     * @var array<string, array|string>
     */
    public array $rawCommits = [];

    /**
     * @var array<string, array|string>
     */
    public array $rawFiles = [];

    abstract public function getCommits(): CommitCollection;

    abstract public function getFiles(): FileCollection;

    abstract public function getComments(): CommentCollection;
}
