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

    /**
     * @var array{'title': string, 'body': string, 'labels': string[], 'assignees': array{'login': string}[], 'requested_reviewers': array{'login': string}[], 'created_at': string, 'updated_at': string}
     */
    public array $raw;

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

        /** @var array{'title': string, 'body': string, 'labels': string[], 'assignees': array{'login': string}[], 'requested_reviewers': array{'login': string}[], 'created_at': string, 'updated_at': string} $raw */
        $raw = $this->client->pullRequest()->show($owner, $repository, (int) $id);
        $this->raw = $raw;

        $this->pullRequest = new GithubPullRequest($this->client, $owner, $repository);
        $this->pullRequest->id = $id;
        $this->pullRequest->projectIdentifier = $projectIdentifier;
        $this->pullRequest->title = $this->raw['title'];
        $this->pullRequest->body = $this->raw['body'];
        $this->pullRequest->labels = array_map(static function (array $label): string {
            return $label['name'];
        }, $this->raw['labels']
        );
        $this->pullRequest->assignees = array_map(static function (array $assignee): string {
            return $assignee['login'];
        }, $this->raw['assignees']
        );
        $this->pullRequest->reviewers = $this->getReviews($owner, $repository, $id);
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

        try {
            $this->client->issues()->update(
                $this->githubOwner,
                $this->githubRepository,
                (int) $this->pullRequest->id,
                [
                    'labels' => $this->pullRequest->labels,
                ]
            );
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Resource not accessible by integration')) {
                return;
            }

            throw $e;
        }
    }

    public function removeLabels(string ...$labels): void
    {
        parent::removeLabels(...$labels);

        try {
            $this->client->issues()->update(
                $this->githubOwner,
                $this->githubRepository,
                (int) $this->pullRequest->id,
                [
                    'labels' => $this->pullRequest->labels,
                ]
            );
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Resource not accessible by integration')) {
                return;
            }

            throw $e;
        }
    }

    /**
     * @return string[]
     */
    private function getReviews(string $owner, string $repository, string $id): array
    {
        $requestedReviewers = array_map(static function (array $reviewer): string {
            return $reviewer['login'];
        }, $this->raw['requested_reviewers']);

        $reviewersRequest = $this->client->pullRequest()->reviews()->all($owner, $repository, (int) $id);
        $reviewers = array_map(function (array $reviewer) {
            return $reviewer['user']['login'];
        }, $reviewersRequest);

        return array_unique(array_merge($requestedReviewers, $reviewers));
    }

    public function hasDangerMessage(): bool
    {
        return count($this->commenter->getCommentIds($this->githubOwner, $this->githubRepository, $this->pullRequest->id)) > 0;
    }
}
