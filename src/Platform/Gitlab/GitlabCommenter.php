<?php
declare(strict_types=1);

namespace Danger\Platform\Gitlab;

use Danger\Config;
use Danger\Renderer\HTMLRenderer;
use Gitlab\Client;
use Gitlab\ResultPager;

class GitlabCommenter
{
    public function __construct(private Client $client)
    {
    }

    public function postNote(string $projectIdentifier, int $prId, string $body, Config $config, string $baseUrl): string
    {
        $noteIds = $this->getRelevantNoteIds($projectIdentifier, $prId);

        if ($config->getUpdateCommentMode() === Config::UPDATE_COMMENT_MODE_REPLACE) {
            foreach ($noteIds as $relevantNoteId) {
                $this->client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
            }

            $note = $this->client->mergeRequests()->addNote($projectIdentifier, $prId, $body);

            return $baseUrl . '#note_' . $note['id'];
        }

        if (count($noteIds) === 0) {
            $note = $this->client->mergeRequests()->addNote($projectIdentifier, $prId, $body);

            return $baseUrl . '#note_' . $note['id'];
        }

        $noteId = array_pop($noteIds);
        $this->client->mergeRequests()->updateNote($projectIdentifier, $prId, $noteId, $body);

        foreach ($noteIds as $relevantNoteId) {
            $this->client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
        }

        return $baseUrl . '#note_' . $noteId;
    }

    public function postThread(string $projectIdentifier, int $prId, string $body, Config $config, string $baseUrl): string
    {
        $threadIds = $this->getRelevantThreadIds($projectIdentifier, $prId);

        if ($config->getUpdateCommentMode() === Config::UPDATE_COMMENT_MODE_REPLACE) {
            foreach ($threadIds as $threadId) {
                $this->client->mergeRequests()->removeDiscussionNote($projectIdentifier, $prId, $threadId['threadId'], $threadId['noteId']);
            }

            $thread = $this->client->mergeRequests()->addDiscussion($projectIdentifier, $prId, ['body' => $body]);

            return $baseUrl . '#note_' . $thread['notes'][0]['id'];
        }

        if (count($threadIds)) {
            $foundThread = $threadIds[0];

            $this->client->mergeRequests()->updateDiscussionNote($projectIdentifier, $prId, $foundThread['threadId'], $foundThread['noteId'], ['body' => $body]);

            if ($foundThread['noteBody'] !== $body) {
                $this->client->mergeRequests()->updateDiscussionNote($projectIdentifier, $prId, $foundThread['threadId'], $foundThread['noteId'], ['resolved' => false]);
            }

            return $baseUrl . '#note_' . $foundThread['noteId'];
        }

        $thread = $this->client->mergeRequests()->addDiscussion($projectIdentifier, $prId, ['body' => $body]);

        return $baseUrl . '#note_' . $thread['notes'][0]['id'];
    }

    public function removeNote(string $projectIdentifier, int $prId): void
    {
        foreach ($this->getRelevantNoteIds($projectIdentifier, $prId) as $relevantNoteId) {
            $this->client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
        }
    }

    public function removeThread(string $projectIdentifier, int $prId): void
    {
        foreach ($this->getRelevantThreadIds($projectIdentifier, $prId) as $threadId) {
            $this->client->mergeRequests()->removeDiscussionNote($projectIdentifier, $prId, $threadId['threadId'], $threadId['noteId']);
        }
    }

    /**
     * @return int[]
     */
    public function getRelevantNoteIds(string $projectIdentifier, int $prId): array
    {
        $pager = new ResultPager($this->client, 100);
        $notes = $pager->fetchAll($this->client->mergeRequests(), 'showNotes', [$projectIdentifier, $prId]);

        $ids = [];

        foreach ($notes as $note) {
            if ($note['system']) {
                continue;
            }

            if (str_contains($note['body'], HTMLRenderer::MARKER)) {
                $ids[] = (int) $note['id'];
            }
        }

        return $ids;
    }

    /**
     * @return array{'threadId': string, 'noteId': int, 'noteBody': string}[]
     */
    private function getRelevantThreadIds(string $projectIdentifier, int $prId): array
    {
        $pager = new ResultPager($this->client, 100);
        $threads = $pager->fetchAll($this->client->mergeRequests(), 'showDiscussions', [$projectIdentifier, $prId]);

        $ids = [];

        foreach ($threads as $thread) {
            if (str_contains($thread['notes'][0]['body'], HTMLRenderer::MARKER)) {
                $ids[] = [
                    'threadId' => $thread['id'],
                    'noteId' => (int) $thread['notes'][0]['id'],
                    'noteBody' => $thread['notes'][0]['body'],
                ];
            }
        }

        return $ids;
    }
}
