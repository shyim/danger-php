<?php
declare(strict_types=1);

namespace Danger\Struct\Gitlab;

use Danger\Struct\Comment;
use Danger\Struct\CommentCollection;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\File;
use Danger\Struct\FileCollection;
use Danger\Struct\Gitlab\File as GitlabFile;
use DateTime;
use Gitlab\Client;
use Gitlab\ResultPager;

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

    public function __construct(private Client $client, private string $latestSha)
    {
    }

    public function getCommits(): CommitCollection
    {
        if ($this->commits !== null) {
            return $this->commits;
        }

        $this->rawCommits = $this->client->mergeRequests()->commits($this->projectIdentifier, (int) $this->id);

        $collection = new CommitCollection();

        foreach ($this->rawCommits as $rawGithubCommit) {
            $commit = new Commit();
            $commit->sha = $rawGithubCommit['id'];
            $commit->createdAt = new DateTime($rawGithubCommit['committed_date']);
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
        if ($this->files !== null) {
            return $this->files;
        }

        $this->rawFiles = $this->client->mergeRequests()->changes($this->projectIdentifier, (int) $this->id);

        $collection = new FileCollection();

        foreach ($this->rawFiles['changes'] as $rawGitlabFile) {
            $file = new GitlabFile($this->client, $this->projectIdentifier, $rawGitlabFile['new_path'], $this->latestSha);
            $file->name = $rawGitlabFile['new_path'];
            $file->status = $this->getState($rawGitlabFile);
            $file->additions = 0;
            $file->deletions = 0;
            $file->changes = $file->additions + $file->deletions;

            if (isset($rawGitlabFile['diff'])) {
                $file->patch = $rawGitlabFile['diff'];
            }

            $collection->set($file->name, $file);
        }

        return $this->files = $collection;
    }

    public function getComments(): CommentCollection
    {
        if ($this->comments !== null) {
            return $this->comments;
        }

        $this->comments = new CommentCollection();

        $pager = new ResultPager($this->client);
        $comments = $pager->fetchAll($this->client->mergeRequests(), 'showNotes', [$this->projectIdentifier, (int) $this->id]);

        foreach ($comments as $commentArray) {
            if ($commentArray['system']) {
                continue;
            }

            $comment = new Comment();
            $comment->author = $commentArray['author']['username'];
            $comment->body = $commentArray['body'];
            $comment->createdAt = new DateTime($commentArray['created_at']);
            $comment->updatedAt = new DateTime($commentArray['updated_at']);

            $this->comments->add($comment);
        }

        return $this->comments;
    }

    /**
     * @param array{'new_file': bool, 'deleted_file': bool} $rawGitlabFile
     * @return string
     */
    private function getState(array $rawGitlabFile): string
    {
        if ($rawGitlabFile['new_file']) {
            return File::STATUS_ADDED;
        }

        if ($rawGitlabFile['deleted_file']) {
            return File::STATUS_REMOVED;
        }

        return File::STATUS_MODIFIED;
    }
}
