<?php
declare(strict_types=1);

namespace Danger\Component\Platform\Github;

use Danger\Component\Platform\AbstractPlatform;
use Danger\Component\Struct\Github\PullRequest as GithubPullRequest;
use Danger\Component\Struct\PullRequest;
use Danger\Config;
use Github\Client;

class Github extends AbstractPlatform
{
    public PullRequest $pullRequest;

    public array $rawGithubPullRequest = [];

    private string $githubOwner;
    private string $githubRepository;

    public function __construct(private Client $client, private GithubCommenter $commenter)
    {
    }

    public function load(string $owner, string $repository, string $id): void
    {
        $this->githubOwner = $owner;
        $this->githubRepository = $repository;

        $this->rawGithubPullRequest = $this->client->pullRequest()->show($owner, $repository, (int) $id);

        $this->pullRequest = new GithubPullRequest($this->client, $owner, $repository);
        $this->pullRequest->id = $id;
        $this->pullRequest->title = $this->rawGithubPullRequest['title'];
        $this->pullRequest->body = $this->rawGithubPullRequest['body'];
        $this->pullRequest->additionsAmount = $this->rawGithubPullRequest['additions'];
        $this->pullRequest->deletionsAmount = $this->rawGithubPullRequest['deletions'];
        $this->pullRequest->changedFilesAmount = $this->rawGithubPullRequest['changed_files'];
        $this->pullRequest->labels = array_map(function (array $label) { return $label['name']; }, $this->rawGithubPullRequest['labels']);
        $this->pullRequest->assignees = array_map(function (array $assignee) { return $assignee['login']; }, $this->rawGithubPullRequest['assignees']);
        $this->pullRequest->createdAt = new \DateTime($this->rawGithubPullRequest['created_at']);
        $this->pullRequest->updatedAt = new \DateTime($this->rawGithubPullRequest['updated_at']);
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
}
