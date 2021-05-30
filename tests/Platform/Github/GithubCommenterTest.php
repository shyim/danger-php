<?php
declare(strict_types=1);

namespace Danger\Tests\Platform\Github;

use Danger\Config;
use Danger\Platform\Github\GithubCommenter;
use Github\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
class GithubCommenterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['GITHUB_TOKEN'] = 'test';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['GITHUB_TOKEN']);
    }

    public function testCommentUsingProxyFails(): void
    {
        $client = new MockHttpClient([
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        static::expectException(\RuntimeException::class);

        $commenter = new GithubCommenter($this->createMock(Client::class), $client);
        $commenter->comment(
            'test',
            'test',
            'test',
            'test',
            (new Config())->useGithubCommentProxy('http://localhost')
        );
    }

    public function testCommentUsingProxy(): void
    {
        $client = new MockHttpClient([
            new MockResponse('{"html_url": "http://test.de"}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter($this->createMock(Client::class), $client);
        static::assertSame('http://test.de', $commenter->comment(
            'test',
            'test',
            'test',
            'test',
            (new Config())->useGithubCommentProxy('http://localhost')
        ));
    }

    public function testRemoveCommentUsingProxy(): void
    {
        $client = new MockHttpClient([
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter($this->createMock(Client::class), $client);
        $commenter->remove(
            'test',
            'test',
            'test',
            (new Config())->useGithubCommentProxy('http://localhost')
        );

        static::assertSame(1, $client->getRequestsCount());
    }

    public function testCommentNew(): void
    {
        $client = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/comments.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"html_url": "http://test.de"}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter(Client::createWithHttpClient(new Psr18Client($client)), $client);
        static::assertSame('http://test.de', $commenter->comment(
            'test',
            'test',
            'test',
            'test',
            new Config()
        ));
    }

    public function testCommentUpdate(): void
    {
        $client = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/comments_containg_danger.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"html_url": "http://test.de"}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter(Client::createWithHttpClient(new Psr18Client($client)), $client);
        static::assertSame('http://test.de', $commenter->comment(
            'test',
            'test',
            'test',
            'test',
            new Config()
        ));
    }

    public function testCommentReplace(): void
    {
        $client = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/comments_containg_danger.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"html_url": "http://test.de"}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter(Client::createWithHttpClient(new Psr18Client($client)), $client);
        static::assertSame('http://test.de', $commenter->comment(
            'test',
            'test',
            'test',
            'test',
            (new Config())->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE)
        ));
    }

    public function testRemove(): void
    {
        $client = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/comments_containg_danger.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $commenter = new GithubCommenter(Client::createWithHttpClient(new Psr18Client($client)), $client);
        $commenter->remove(
            'test',
            'test',
            'test',
            (new Config())->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE)
        );

        static::assertSame(3, $client->getRequestsCount());
    }
}
