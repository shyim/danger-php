<?php
declare(strict_types=1);

namespace Danger\Platform\Gitlab;

use function count;

use Danger\Config;
use Danger\Platform\AbstractPlatform;
use Danger\Struct\Gitlab\PullRequest;
use DateTime;
use Gitlab\Client;

/**
 * @property array{'sha': string, 'title': string, 'web_url': string, 'description': string|null, 'labels': string[], 'assignees': array{'username': string}[], 'reviewers': array{'username': string}[], 'created_at': string, 'updated_at': string} $raw
 */
class Gitlab extends AbstractPlatform
{
    private string $projectIdentifier;

    public function __construct(public Client $client, private GitlabCommenter $commenter)
    {
    }

    public function load(string $projectIdentifier, string $id): void
    {
        $this->projectIdentifier = $projectIdentifier;

        /** @var array{'sha': string, 'title': string, 'web_url': string, 'description': string|null, 'labels': string[], 'assignees': array{'username': string}[], 'reviewers': array{'username': string}[], 'created_at': string, 'updated_at': string} $res */
        $res = $this->client->mergeRequests()->show($projectIdentifier, (int) $id);
        $this->raw = $res;

        $this->pullRequest = new PullRequest($this->client, $this->raw['sha']);
        $this->pullRequest->id = $id;
        $this->pullRequest->projectIdentifier = $projectIdentifier;
        $this->pullRequest->title = $this->raw['title'];
        $this->pullRequest->body = (string) $this->raw['description'];
        $this->pullRequest->labels = $this->raw['labels'];
        $this->pullRequest->assignees = array_map(static function (array $assignee) { return $assignee['username']; }, $this->raw['assignees']);
        $this->pullRequest->reviewers = array_map(static function (array $reviewer) { return $reviewer['username']; }, $this->raw['reviewers']);
        $this->pullRequest->createdAt = new DateTime($this->raw['created_at']);
        $this->pullRequest->updatedAt = new DateTime($this->raw['updated_at']);
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

    public function hasDangerMessage(): bool
    {
        return count($this->commenter->getRelevantNoteIds($this->projectIdentifier, (int) $this->pullRequest->id)) > 0;
    }
}
