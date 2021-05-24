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

    public array $raw = [];

    public function __construct(public Client $client, private GitlabCommenter $commenter)
    {
    }

    public function load(string $projectIdentifier, string $id): void
    {
        $this->projectIdentifier = $projectIdentifier;

        $this->raw = $this->client->mergeRequests()->show($projectIdentifier, (int) $id);

        $this->pullRequest = new PullRequest($this->client, $this->raw['sha']);
        $this->pullRequest->id = $id;
        $this->pullRequest->projectIdentifier = $projectIdentifier;
        $this->pullRequest->title = $this->raw['title'];
        $this->pullRequest->body = $this->raw['description'];
        $this->pullRequest->labels = $this->raw['labels'];
        $this->pullRequest->assignees = array_map(function (array $assignee) { return $assignee['username']; }, $this->raw['assignees']);
        $this->pullRequest->createdAt = new \DateTime($this->raw['created_at']);
        $this->pullRequest->updatedAt = new \DateTime($this->raw['updated_at']);
    }

    public function post(string $body, Config $config): string
    {
        if ($config->isThreadEnabled()) {
            return $this->commenter->postThread(
                $this->projectIdentifier,
                (int) $this->pullRequest->id,
                $body,
                $config,
                $this->raw['web_url']
            );
        }

        return $this->commenter->postNote(
            $this->projectIdentifier,
            (int) $this->pullRequest->id,
            $body,
            $config,
            $this->raw['web_url']
        );
    }

    public function removePost(Config $config): void
    {
        if ($config->isThreadEnabled()) {
            $this->commenter->removeThread($this->projectIdentifier, (int) $this->pullRequest->id);

            return;
        }

        $this->commenter->removeNote($this->projectIdentifier, (int) $this->pullRequest->id);
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
