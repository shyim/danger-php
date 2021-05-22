<?php
declare(strict_types=1);

namespace Danger\Component\Struct\Gitlab;

use Danger\Component\Struct\Commit;
use Danger\Component\Struct\CommitCollection;
use Danger\Component\Struct\FileCollection;
use Gitlab\Client;

class PullRequest extends \Danger\Component\Struct\PullRequest
{
    private array $rawGitlabCommits = [];
    private array $rawGitlabFiles = [];
    private ?CommitCollection $commits = null;
    private ?FileCollection $files = null;

    public function __construct(private Client $client, private string $projectIdentifier, private string $latestSha)
    {
    }

    public function getCommits(): CommitCollection
    {
        if ($this->commits) {
            return $this->commits;
        }

        $this->rawGitlabCommits = $this->client->mergeRequests()->commits($this->projectIdentifier, (int) $this->id);

        $collection = new CommitCollection();

        foreach ($this->rawGitlabCommits as $rawGithubCommit) {
            $commit = new Commit();
            $commit->sha = $rawGithubCommit['id'];
            $commit->createdAt = new \DateTime($rawGithubCommit['committed_date']);
            $commit->message = $rawGithubCommit['message'];
            $commit->author = $rawGithubCommit['author_name'];
            $commit->authorEmail = $rawGithubCommit['author_email'];
            $commit->verified = false;

            $collection->add($commit);
        }

        return $this->commits = $collection;
    }

    public function getFiles(): FileCollection
    {
        if ($this->files) {
            return $this->files;
        }

        $this->rawGitlabFiles = $this->client->mergeRequests()->changes($this->projectIdentifier, (int) $this->id);

        $collection = new FileCollection();

        foreach ($this->rawGitlabFiles['changes'] as $rawGithubFile) {
            $file = new File($this->client, $this->projectIdentifier, $rawGithubFile['new_path'], $this->latestSha);
            $file->name = $rawGithubFile['new_path'];
            $file->status = $rawGithubFile['new_file'] ? File::STATUS_ADDED : ($rawGithubFile['deleted_file'] ? File::STATUS_REMOVED : File::STATUS_MODIFIED);
            $file->additions = 0;
            $file->deletions = 0;
            $file->changes = $file->additions + $file->deletions;

            $collection->add($file);
        }

        return $this->files = $collection;
    }
}
