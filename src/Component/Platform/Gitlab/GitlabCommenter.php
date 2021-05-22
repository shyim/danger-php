<?php
declare(strict_types=1);

namespace Danger\Component\Platform\Gitlab;

use Danger\Component\Renderer\HTMLRenderer;
use Danger\Config;
use Gitlab\Client;

class GitlabCommenter
{
    public function postNote(Client $client, string $projectIdentifier, int $prId, string $body, Config $config, string $baseUrl): string
    {
        $noteIds = $this->getRelevantNoteIds($client, $projectIdentifier, $prId);

        if ($config->getUpdateCommentMode() === Config::UPDATE_COMMENT_MODE_REPLACE) {
            foreach ($noteIds as $relevantNoteId) {
                $client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
            }

            $note = $client->mergeRequests()->addNote($projectIdentifier, $prId, $body);

            return $baseUrl . '#note_' . $note['id'];
        }

        if (count($noteIds) === 0) {
            $note = $client->mergeRequests()->addNote($projectIdentifier, $prId, $body);

            return $baseUrl . '#note_' . $note['id'];
        }

        $noteId = array_pop($noteIds);
        $client->mergeRequests()->updateNote($projectIdentifier, $prId, $noteId, $body);

        foreach ($noteIds as $relevantNoteId) {
            $client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
        }

        return $baseUrl . '#note_' . $noteId;
    }

    public function postThread(Client $client, string $projectIdentifier, int $prId, string $body, Config $config, string $baseUrl): string
    {
        $threadIds = $this->getRelevantThreadIds($client, $projectIdentifier, $prId);

        if ($config->getUpdateCommentMode() === Config::UPDATE_COMMENT_MODE_REPLACE) {
            foreach ($threadIds as $threadId) {
                $client->mergeRequests()->removeDiscussionNote($projectIdentifier, $prId, $threadId[0], $threadId[1]);
            }

            $thread = $client->mergeRequests()->addDiscussion($projectIdentifier, $prId, ['body' => $body]);

            return $baseUrl . '#note_' . $thread['notes'][0]['id'];
        }

        if (count($threadIds)) {
            $client->mergeRequests()->updateDiscussionNote($projectIdentifier, $prId, $threadIds[0][0], $threadIds[0][1], ['body' => $body]);
            $client->mergeRequests()->updateDiscussionNote($projectIdentifier, $prId, $threadIds[0][0], $threadIds[0][1], ['resolved' => false]);

            return $baseUrl . '#note_' . $threadIds[0][1];
        }

        $thread = $client->mergeRequests()->addDiscussion($projectIdentifier, $prId, ['body' => $body]);

        return $baseUrl . '#note_' . $thread['notes'][0]['id'];
    }

    public function removeNote(Client $client, string $projectIdentifier, int $prId): void
    {
        foreach ($this->getRelevantNoteIds($client, $projectIdentifier, $prId) as $relevantNoteId) {
            $client->mergeRequests()->removeNote($projectIdentifier, $prId, $relevantNoteId);
        }
    }

    public function removeThread(Client $client, string $projectIdentifier, int $prId): void
    {
        foreach ($this->getRelevantThreadIds($client, $projectIdentifier, $prId) as $threadId) {
            $client->mergeRequests()->removeDiscussionNote($projectIdentifier, $prId, $threadId[0], $threadId[1]);
        }
    }

    private function getRelevantNoteIds(Client $client, string $projectIdentifier, int $prId): array
    {
        $notes = $client->mergeRequests()->showNotes($projectIdentifier, $prId);

        $ids = [];

        foreach ($notes as $note) {
            if ($note['system']) {
                continue;
            }

            if (str_contains($note['body'], HTMLRenderer::MARKER)) {
                $ids[] = $note['id'];
            }
        }

        return $ids;
    }

    private function getRelevantThreadIds(Client $client, string $projectIdentifier, int $prId): array
    {
        $threads = $client->mergeRequests()->showDiscussions($projectIdentifier, $prId);

        $ids = [];

        foreach ($threads as $thread) {
            if ($thread['individual_note']) {
                continue;
            }

            if ($thread['notes'][0]['type'] !== 'DiscussionNote') {
                continue;
            }

            if (str_contains($thread['notes'][0]['body'], HTMLRenderer::MARKER)) {
                $ids[] = [$thread['id'], $thread['notes'][0]['id']];
            }
        }

        return $ids;
    }
}
