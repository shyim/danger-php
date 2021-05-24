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

    public array $assignees;

    public array $rawCommits = [];
    public array $rawFiles = [];

    abstract public function getCommits(): CommitCollection;

    abstract public function getFiles(): FileCollection;
}
