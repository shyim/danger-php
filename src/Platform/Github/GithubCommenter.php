<?php
declare(strict_types=1);

namespace Danger\Platform\Github;

use function count;
use Danger\Config;
use Danger\Renderer\HTMLRenderer;
use Github\Client;
use Github\ResultPager;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubCommenter
{
    public function __construct(private Client $client, private HttpClientInterface $httpClient)
    {
    }

    public function comment(string $owner, string $repo, string $id, string $body, Config $config): string
    {
        if ($config->getGithubCommentProxy() !== null) {
            return $this->commentUsingProxy($owner, $repo, $id, $body, $config);
        }

        return $this->commentUsingApiKey($owner, $repo, $id, $body, $config);
    }

    private function commentUsingProxy(string $owner, string $repo, string $id, string $body, Config $config): string
    {
        $url = sprintf('%s/repos/%s/%s/issues/%s/comments', $config->getGithubCommentProxy(), $owner, $repo, $id);
        $response = $this->httpClient->request('POST', $url, [
            'json' => ['body' => $body, 'mode' => $config->getUpdateCommentMode()],
            'headers' => [
                'User-Agent' => 'Comment-Proxy',
                'temporary-github-token' => $_SERVER['GITHUB_TOKEN'],
            ],
        ])->toArray();

        if (!isset($response['html_url'])) {
            throw new RuntimeException(sprintf('Expected html_url in the response. But got %s', json_encode($response, \JSON_THROW_ON_ERROR)));
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

            $comment = $this->client->issues()->comments()->create($owner, $repo, (int) $id, ['body' => $body]);

            return $comment['html_url'];
        }

        /**
         * Could not find any comment. Lets create a new one
         */
        if (count($ids) === 0) {
            $comment = $this->client->issues()->comments()->create($owner, $repo, (int) $id, ['body' => $body]);

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

    /**
     * @return int[]
     */
    public function getCommentIds(string $owner, string $repo, string $id): array
    {
        $ids = [];

        $pager = new ResultPager($this->client);
        $comments = $pager->fetchAll($this->client->issues()->comments(), 'all', [$owner, $repo, (int) $id]);

        foreach ($comments as $comment) {
            if (str_contains($comment['body'], HTMLRenderer::MARKER)) {
                $ids[] = (int) $comment['id'];
            }
        }

        return $ids;
    }

    public function remove(string $owner, string $repo, string $id, Config $config): void
    {
        if ($config->getGithubCommentProxy() !== null) {
            $url = sprintf('%s/repos/%s/%s/issues/%s/comments', $config->getGithubCommentProxy(), $owner, $repo, $id);
            $this->httpClient->request('POST', $url, [
                'json' => ['body' => 'delete', 'mode' => $config->getUpdateCommentMode()],
                'headers' => [
                    'User-Agent' => 'Comment-Proxy',
                    'temporary-github-token' => $_SERVER['GITHUB_TOKEN'],
                ],
            ])->toArray();

            return;
        }

        $ids = $this->getCommentIds($owner, $repo, $id);

        foreach ($ids as $commentId) {
            $this->client->issues()->comments()->remove($owner, $repo, $commentId);
        }
    }
}
