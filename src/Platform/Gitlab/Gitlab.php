<?php
declare(strict_types=1);

namespace Danger\Platform\Gitlab;

use Danger\Config;
use Danger\Platform\AbstractPlatform;
use Danger\Struct\Gitlab\PullRequest;
use Gitlab\Client;

class Gitlab extends AbstractPlatform
{
    private string $projectIdentifier;
    private Client $client;

    private array $rawGitlabMergeRequest = [];

    public function __construct(private GitlabCommenter $commenter)
    {
    }

    public function load(string $projectIdentifier, string $id): void
    {
        $this->projectIdentifier = $projectIdentifier;
        $this->client = new Client();

        if (isset($_SERVER['CI_SERVER_URL'])) {
            $this->client->setUrl($_SERVER['CI_SERVER_URL']);
        }

        $this->client->authenticate($_SERVER['DANGER_GITLAB_TOKEN'], Client::AUTH_HTTP_TOKEN);

        $this->rawGitlabMergeRequest = $this->client->mergeRequests()->show($projectIdentifier, (int) $id);

        $this->pullRequest = new PullRequest($this->client, $projectIdentifier, $this->rawGitlabMergeRequest['sha']);
        $this->pullRequest->id = $id;
        $this->pullRequest->title = $this->rawGitlabMergeRequest['title'];
        $this->pullRequest->body = $this->rawGitlabMergeRequest['description'];
        $this->pullRequest->labels = $this->rawGitlabMergeRequest['labels'];
        $this->pullRequest->assignees = array_map(function (array $assignee) { return $assignee['username']; }, $this->rawGitlabMergeRequest['assignees']);
        $this->pullRequest->createdAt = new \DateTime($this->rawGitlabMergeRequest['created_at']);
        $this->pullRequest->updatedAt = new \DateTime($this->rawGitlabMergeRequest['updated_at']);
    }

    public function post(string $body, Config $config): string
    {
        if ($config->isThreadEnabled()) {
            return $this->commenter->postThread(
                $this->client,
                $this->projectIdentifier,
                (int) $this->pullRequest->id,
                $body,
                $config,
                $this->rawGitlabMergeRequest['web_url']
            );
        }

        return $this->commenter->postNote(
            $this->client,
            $this->projectIdentifier,
            (int) $this->pullRequest->id,
            $body,
            $config,
            $this->rawGitlabMergeRequest['web_url']
        );
    }

    public function removePost(Config $config): void
    {
        if ($config->isThreadEnabled()) {
            $this->commenter->removeThread($this->client, $this->projectIdentifier, (int) $this->pullRequest->id);

            return;
        }

        $this->commenter->removeNote($this->client, $this->projectIdentifier, (int) $this->pullRequest->id);
    }

    public function addLabels(string ...$labels): void
    {
        parent::addLabels(...$labels);

        $this->client->mergeRequests()->update($this->projectIdentifier, (int) $this->pullRequest->id, [
            'labels' => implode(',', $this->pullRequest->labels),
        ]);
    }

    public function removeLabels(string ...$labels): void
    {
        parent::removeLabels(...$labels);

        $this->client->mergeRequests()->update($this->projectIdentifier, (int) $this->pullRequest->id, [
            'labels' => implode(',', $this->pullRequest->labels),
        ]);
    }
}
