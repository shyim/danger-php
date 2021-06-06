<?php
declare(strict_types=1);

namespace Danger\Struct\Github;

use Danger\Struct\Comment;
use Danger\Struct\CommentCollection;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\File;
use Danger\Struct\FileCollection;
use Danger\Struct\Github\File as GithubFile;
use Github\Client as GithubClient;
use Github\ResultPager;

class PullRequest extends \Danger\Struct\PullRequest
{
    /**
     * @var CommitCollection<Commit>|null
     */
    private ?CommitCollection $commits = null;

    /**
     * @var FileCollection<File>|null
     */
    private ?FileCollection $files = null;

    /**
     * @var CommentCollection<Comment>|null
     */
    private ?CommentCollection $comments = null;

    public function __construct(private GithubClient $client, private string $owner, private string $repo)
    {
    }

    public function getCommits(): CommitCollection
    {
        if (null !== $this->commits) {
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
        if (null !== $this->files) {
            return $this->files;
        }

        $this->rawFiles = $this->client->pullRequest()->files($this->owner, $this->repo, $this->id);

        $collection = new FileCollection();

        foreach ($this->rawFiles as $rawGithubFile) {
            $file = new GithubFile($rawGithubFile['raw_url']);
            $file->name = $rawGithubFile['filename'];
            $file->status = $rawGithubFile['status'];
            $file->additions = $rawGithubFile['additions'];
            $file->deletions = $rawGithubFile['deletions'];
            $file->changes = $rawGithubFile['changes'];
            $file->patch = $rawGithubFile['patch'];

            $collection->set($file->name, $file);
        }

        return $this->files = $collection;
    }

    public function getComments(): CommentCollection
    {
        if ($this->comments !== null) {
            return $this->comments;
        }

        $pager = new ResultPager($this->client);
        $comments = $pager->fetchAll($this->client->pullRequest()->comments(), 'all', [$this->owner, $this->repo, $this->id]);
        $this->comments = new CommentCollection();

        foreach ($comments as $commentArray) {
            $comment = new Comment();
            $comment->author = $commentArray['user']['login'];
            $comment->body = $commentArray['body'];
            $comment->createdAt = new \DateTime($commentArray['created_at']);
            $comment->updatedAt = new \DateTime($commentArray['updated_at']);

            $this->comments->add($comment);
        }

        return $this->comments;
    }
}
