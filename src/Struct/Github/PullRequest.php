<?php
declare(strict_types=1);

namespace Danger\Struct\Github;

use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\FileCollection;
use Github\Client as GithubClient;

class PullRequest extends \Danger\Struct\PullRequest
{
    private ?CommitCollection $commits = null;
    private ?FileCollection $files = null;

    public function __construct(private GithubClient $client, private string $owner, private string $repo)
    {
    }

    public function getCommits(): CommitCollection
    {
        if ($this->commits) {
            return $this->commits;
        }

        $this->rawCommits = $this->client->pullRequest()->commits($this->owner, $this->repo, $this->id);

        $collection = new CommitCollection();

        foreach ($this->rawCommits as $rawGithubCommit) {
            $commit = new Commit();
            $commit->sha = $rawGithubCommit['sha'];
            $commit->createdAt = new \DateTime($rawGithubCommit['commit']['committer']['date']);
            $commit->message = $rawGithubCommit['commit']['message'];
            $commit->author = $rawGithubCommit['commit']['committer']['name'];
            $commit->authorEmail = $rawGithubCommit['commit']['committer']['email'];
            $commit->verified = $rawGithubCommit['commit']['verification']['verified'];

            $collection->add($commit);
        }

        return $this->commits = $collection;
    }

    public function getFiles(): FileCollection
    {
        if ($this->files) {
            return $this->files;
        }

        $this->rawFiles = $this->client->pullRequest()->files($this->owner, $this->repo, $this->id);

        $collection = new FileCollection();

        foreach ($this->rawFiles as $rawGithubFile) {
            $file = new File($rawGithubFile['raw_url']);
            $file->name = $rawGithubFile['filename'];
            $file->status = $rawGithubFile['status'];
            $file->additions = $rawGithubFile['additions'];
            $file->deletions = $rawGithubFile['deletions'];
            $file->changes = $rawGithubFile['changes'];

            $collection->add($file);
        }

        return $this->files = $collection;
    }
}
