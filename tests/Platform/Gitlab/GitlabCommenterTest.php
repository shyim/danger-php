<?php
declare(strict_types=1);

namespace Danger\Tests\Platform\Gitlab;

use Danger\Config;
use Danger\Platform\Gitlab\GitlabCommenter;
use Gitlab\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
class GitlabCommenterTest extends TestCase
{
    public function testPostNoteNew(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_notes.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"id": 1}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = new GitlabCommenter($client);
        static::assertSame('http://gitlab.com#note_1', $commenter->postNote('test', 1, 'Test', new Config(), 'http://gitlab.com'));
    }

    public function testPostNoteUpdatesFirstDeletesOther(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_notes_multiple_notes.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"id": 1}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = new GitlabCommenter($client);
        static::assertSame('http://gitlab.com#note_582463689', $commenter->postNote('test', 1, 'Test', new Config(), 'http://gitlab.com'));
    }

    public function testPostReplace(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_notes_multiple_notes.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"id": 1}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = new GitlabCommenter($client);
        static::assertSame('http://gitlab.com#note_1', $commenter->postNote('test', 1, 'Test', (new Config())->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE), 'http://gitlab.com'));
    }

    public function testRemovePost(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_notes_multiple_notes.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = new GitlabCommenter($client);
        $commenter->removeNote('test', 1);

        static::assertSame(3, $mockHttpClient->getRequestsCount());
    }

    public function testCreateNewThread(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_threads.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"notes": [{"id": "1"}]}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));
        $config = (new Config())->useThreadOnFails();

        $commenter = new GitlabCommenter($client);
        $url = $commenter->postThread('test', 1, 'test', $config, 'http://gitlab.com');
        static::assertSame('http://gitlab.com#note_1', $url);
    }

    public function testCreateNewThreadAndDeleteOther(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_threads_multiple.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"notes": [{"id": "1"}]}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));
        $config = (new Config())->useThreadOnFails();

        $commenter = new GitlabCommenter($client);
        $url = $commenter->postThread('test', 1, 'test', $config, 'http://gitlab.com');
        static::assertSame('http://gitlab.com#note_582463689', $url);
    }

    public function testReplaceThread(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_threads_multiple.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"notes": [{"id": "1"}]}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));
        $config = (new Config())->useThreadOnFails()->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE);

        $commenter = new GitlabCommenter($client);
        $url = $commenter->postThread('test', 1, 'test', $config, 'http://gitlab.com');
        static::assertSame('http://gitlab.com#note_1', $url);
    }

    public function testRemoveThread(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_threads_multiple.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = new GitlabCommenter($client);
        $commenter->removeThread('test', 1);

        static::assertSame(3, $mockHttpClient->getRequestsCount());
    }
}
