<?php
declare(strict_types=1);

namespace Danger\Tests\Platform\Github;

use Danger\Config;
use Danger\Platform\Github\Github;
use Danger\Platform\Github\GithubCommenter;
use Danger\Struct\File;
use Github\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
class GithubTest extends TestCase
{
    public function testLoad(): void
    {
        $prBody = file_get_contents(__DIR__ . '/payloads/pr.json');
        $httpClient = new MockHttpClient([
            new MockResponse($prBody, ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse(file_get_contents(__DIR__ . '/payloads/commits.json'), ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse(file_get_contents(__DIR__ . '/payloads/files.json'), ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($httpClient));

        $github = new Github($client, new GithubCommenter($client, new MockHttpClient()));
        $github->load('FriendsOfShopware/FroshPluginUploader', '144');

        static::assertSame(json_decode($prBody, true), $github->raw);
        static::assertSame('144', $github->pullRequest->id);
        static::assertSame('Test PR commenting', $github->pullRequest->title);
        static::assertSame('Body', $github->pullRequest->body);
        static::assertSame([], $github->pullRequest->labels);
        static::assertSame([], $github->pullRequest->assignees);
        static::assertSame(1621542059, $github->pullRequest->createdAt->getTimestamp());
        static::assertSame(1621547349, $github->pullRequest->updatedAt->getTimestamp());

        $commits = $github->pullRequest->getCommits();
        static::assertSame($commits, $github->pullRequest->getCommits());
        static::assertCount(1, $commits);

        $commit = $commits->first();
        static::assertSame('fix(ci): Fix commit linting for external', $commit->message);
        static::assertSame('Soner Sayakci', $commit->author);
        static::assertSame('s.sayakci@shopware.com', $commit->authorEmail);
        static::assertSame(1621547082, $commit->createdAt->getTimestamp());
        static::assertSame('04911c4a084c06d8edac20cff34c236329175c66', $commit->sha);
        static::assertFalse($commit->verified);

        $files = $github->pullRequest->getFiles();
        static::assertSame($files, $github->pullRequest->getFiles());

        static::assertCount(4, $files);

        $file = $files->first();
        static::assertSame('.github/checks.php', $file->name);
        static::assertSame(File::STATUS_ADDED, $file->status);
        static::assertSame(10, $file->additions);
        static::assertSame(0, $file->deletions);
        static::assertSame(10, $file->changes);
        static::assertStringContainsString('Verify commit', $file->getContent());
    }

    public function testPost(): void
    {
        $commenter = $this->createMock(GithubCommenter::class);
        $commenter->expects(static::once())->method('comment')->willReturn('http://github.com');

        $httpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/payloads/pr.json'), ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($httpClient));

        $github = new Github($client, $commenter);

        $github->load('FriendsOfShopware/FroshPluginUploader', '144');

        static::assertSame('http://github.com', $github->post('test', new Config()));
    }

    public function testRemovePost(): void
    {
        $commenter = $this->createMock(GithubCommenter::class);
        $commenter->expects(static::once())->method('remove');

        $httpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/payloads/pr.json'), ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($httpClient));

        $github = new Github($client, $commenter);

        $github->load('FriendsOfShopware/FroshPluginUploader', '144');

        $github->removePost(new Config());
    }

    public function testLabels(): void
    {
        $commenter = $this->createMock(GithubCommenter::class);

        $httpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/payloads/pr.json'), ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($httpClient));

        $github = new Github($client, $commenter);

        $github->load('FriendsOfShopware/FroshPluginUploader', '144');

        static::assertSame([], $github->pullRequest->labels);
        $github->addLabels('Test');
        static::assertSame(['Test'], $github->pullRequest->labels);
        $github->removeLabels('Test');
        static::assertSame([], $github->pullRequest->labels);
    }
}
