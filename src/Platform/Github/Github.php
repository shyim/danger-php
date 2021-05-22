<?php
declare(strict_types=1);

namespace Danger\Platform\Github;

use Danger\Config;
use Danger\Platform\AbstractPlatform;
use Danger\Struct\Github\PullRequest as GithubPullRequest;
use Danger\Struct\PullRequest;
use Github\Client;

class Github extends AbstractPlatform
{
    public PullRequest $pullRequest;

    public array $raw = [];

    private string $githubOwner;
    private string $githubRepository;

    public function __construct(public Client $client, private GithubCommenter $commenter)
    {
    }

    public function load(string $projectIdentifier, string $id): void
    {
        [$owner, $repository] = explode('/', $projectIdentifier);

        $this->githubOwner = $owner;
        $this->githubRepository = $repository;

        $this->raw = $this->client->pullRequest()->show($owner, $repository, (int) $id);

        $this->pullRequest = new GithubPullRequest($this->client, $owner, $repository);
        $this->pullRequest->id = $id;
        $this->pullRequest->title = $this->raw['title'];
        $this->pullRequest->body = $this->raw['body'];
        $this->pullRequest->labels = array_map(function (array $label) { return $label['name']; }, $this->raw['labels']);
        $this->pullRequest->assignees = array_map(function (array $assignee) { return $assignee['login']; }, $this->raw['assignees']);
        $this->pullRequest->createdAt = new \DateTime($this->raw['created_at']);
        $this->pullRequest->updatedAt = new \DateTime($this->raw['updated_at']);
    }

    public function post(string $body, Config $config): string
    {
        return $this->commenter->comment(
            $this->githubOwner,
            $this->githubRepository,
            $this->pullRequest->id,
            $body,
            $config
        );
    }

    public function removePost(Config $config): void
    {
        $this->commenter->remove(
            $this->githubOwner,
            $this->githubRepository,
            $this->pullRequest->id,
            $config
        );
    }

    public function addLabels(string ...$labels): void
    {
        parent::addLabels(...$labels);

        $this->client->issues()->update(
            $this->githubOwner,
            $this->githubRepository,
            $this->pullRequest->id,
            [
                'labels' => $this->pullRequest->labels,
            ]
        );
    }

    public function removeLabels(string ...$labels): void
    {
        parent::removeLabels(...$labels);

        $this->client->issues()->update(
            $this->githubOwner,
            $this->githubRepository,
            $this->pullRequest->id,
            [
                'labels' => $this->pullRequest->labels,
            ]
        );
    }
}
