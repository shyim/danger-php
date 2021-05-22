<?php
declare(strict_types=1);

namespace Danger\Component\Platform\Github;

use Danger\Component\Renderer\HTMLRenderer;
use Danger\Config;
use Github\Client;

class GithubCommenter
{
    public function __construct(private Client $client)
    {
    }

    public function comment(string $owner, string $repo, string $id, string $body, Config $config): string
    {
        if ($config->getGithubCommentProxy()) {
            return $this->commentUsingProxy($owner, $repo, $id, $body, $config);
        }

        return $this->commentUsingApiKey($owner, $repo, $id, $body, $config);
    }

    private function commentUsingProxy(string $owner, string $repo, string $id, string $body, Config $config): string
    {
        $ch = curl_init(sprintf('%s/repos/%s/%s/issues/%s/comments', $config->getGithubCommentProxy(), $owner, $repo, $id));
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_HTTPHEADER, [
            'user-agent: Comment-Proxy',
            'content-type: application/json',
            'temporary-github-token: ' . $_SERVER['GITHUB_TOKEN'],
        ]);
        curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode(['body' => $body, 'mode' => $config->getUpdateCommentMode()]));

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($response['html_url'])) {
            throw new \RuntimeException(sprintf('Expected html_url in the response. But got %s', json_encode($response)));
        }

        return $response['html_url'];
    }

    private function commentUsingApiKey(string $owner, string $repo, string $id, string $body, Config $config): string
    {
        $ids = $this->getCommentIds($owner, $repo, $id);

        /**
         * Delete all comments and create a new one
         */
        if ($config->getUpdateCommentMode() === Config::UPDATE_COMMENT_MODE_REPLACE) {
            foreach ($ids as $commentId) {
                $this->client->issues()->comments()->remove($owner, $repo, $commentId);
            }

            $comment = $this->client->issues()->comments()->create($owner, $repo, $id, ['body' => $body]);

            return $comment['html_url'];
        }

        /**
         * Could not find any comment. Lets create a new one
         */
        if (count($ids) === 0) {
            $comment = $this->client->issues()->comments()->create($owner, $repo, $id, ['body' => $body]);

            return $comment['html_url'];
        }

        $url = '';

        /**
         * Update first comment, delete all other
         */
        foreach ($ids as $i => $commentId) {
            if ($i === 0) {
                $comment = $this->client->issues()->comments()->update($owner, $repo, $commentId, ['body' => $body]);

                $url = $comment['html_url'];
                continue;
            }

            $this->client->issues()->comments()->remove($owner, $repo, $commentId);
        }

        return $url;
    }

    private function getCommentIds(string $owner, string $repo, string $id): array
    {
        $ids = [];

        $comments = $this->client->issues()->comments()->all($owner, $repo, $id);

        foreach ($comments as $comment) {
            if (str_contains($comment['body'], HTMLRenderer::MARKER)) {
                $ids[] = $comment['id'];
            }
        }

        return $ids;
    }

    public function remove(string $owner, string $repo, string $id, Config $config): void
    {
        if ($config->getGithubCommentProxy()) {
            $ch = curl_init(sprintf('%s/repos/%s/%s/issues/%s/comments', $config->getGithubCommentProxy(), $owner, $repo, $id));
            curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, \CURLOPT_HTTPHEADER, [
                'user-agent: Comment-Proxy',
                'content-type: application/json',
                'temporary-github-token: ' . $_SERVER['GITHUB_TOKEN'],
            ]);
            curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode(['body' => 'delete', 'mode' => $config->getUpdateCommentMode()]));

            curl_exec($ch);
            curl_close($ch);

            return;
        }

        $ids = $this->getCommentIds($owner, $repo, $id);

        foreach ($ids as $commentId) {
            $this->client->issues()->comments()->remove($owner, $repo, $commentId);
        }
    }
}
