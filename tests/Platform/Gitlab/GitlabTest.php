<?php
declare(strict_types=1);

namespace Danger\Tests\Platform\Gitlab;

use Danger\Config;
use Danger\Exception\CouldNotGetFileContentException;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Platform\Gitlab\GitlabCommenter;
use Danger\Struct\Comment;
use Danger\Struct\Commit;
use Danger\Struct\File;
use Gitlab\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
class GitlabTest extends TestCase
{
    public function testLoad(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/commits.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/list_notes.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $gitlab = new Gitlab($client, new GitlabCommenter($client));
        $gitlab->load('test', '1');

        static::assertSame(json_decode((string) file_get_contents(__DIR__ . '/payloads/mr.json'), true, 512, \JSON_THROW_ON_ERROR), $gitlab->raw);
        static::assertSame('1', $gitlab->pullRequest->id);
        static::assertSame('test', $gitlab->pullRequest->projectIdentifier);
        static::assertSame('Update Test', $gitlab->pullRequest->title);
        static::assertSame('Bodyyy', $gitlab->pullRequest->body);
        static::assertSame(['Test'], $gitlab->pullRequest->labels);
        static::assertSame(['shyim'], $gitlab->pullRequest->assignees);
        static::assertSame(['dangertestuser', 'dangertestuser2'], $gitlab->pullRequest->reviewers);
        static::assertSame(1_621_638_766, $gitlab->pullRequest->createdAt->getTimestamp());
        static::assertSame(1_621_672_778, $gitlab->pullRequest->updatedAt->getTimestamp());

        $commits = $gitlab->pullRequest->getCommits();
        static::assertSame($commits, $gitlab->pullRequest->getCommits());
        static::assertCount(5, $commits);

        $commit = $commits->first();
        static::assertInstanceOf(Commit::class, $commit);
        static::assertSame('Add new file', $commit->message);
        static::assertSame('Shyim', $commit->author);
        static::assertSame('s.sayakci@gmail.com', $commit->authorEmail);
        static::assertSame(1_621_672_778, $commit->createdAt->getTimestamp());
        static::assertSame('2d7f9727fb1a786543df555bb55ad4febeeb2f2f', $commit->sha);
        static::assertFalse($commit->verified);

        $comments = $gitlab->pullRequest->getComments();
        static::assertSame($comments, $gitlab->pullRequest->getComments());

        $comment = $comments->first();

        static::assertInstanceOf(Comment::class, $comment);
        static::assertCount(1, $comments);
        static::assertSame('shyim', $comment->author);
        static::assertStringContainsString('<table>', $comment->body);
        static::assertSame(1_621_672_794, $comment->createdAt->getTimestamp());
        static::assertSame(1_621_672_794, $comment->updatedAt->getTimestamp());
    }

    public function testFiles(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/files.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"content": "VGVzdA=="}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"content2": "VGVzdA=="}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $gitlab = new Gitlab($client, new GitlabCommenter($client));
        $gitlab->load('test', '1');

        $files = $gitlab->pullRequest->getFiles();
        static::assertSame($files, $gitlab->pullRequest->getFiles());

        static::assertCount(4, $files);

        $file = $files->first();
        static::assertInstanceOf(File::class, $file);
        static::assertSame('.danger.php', $file->name);
        static::assertSame(File::STATUS_ADDED, $file->status);
        static::assertSame(0, $file->additions);
        static::assertSame(0, $file->deletions);
        static::assertSame(0, $file->changes);
        static::assertSame('Test', $file->getContent());
        static::assertSame('Test', $file->getContent());

        static::expectException(\InvalidArgumentException::class);

        $lastFile = $files->last();
        static::assertInstanceOf(File::class, $lastFile);
        static::assertSame(File::STATUS_REMOVED, $lastFile->status);

        $lastFile->getContent();
    }

    public function testPost(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = $this->createMock(GitlabCommenter::class);
        $commenter->expects(static::once())->method('postThread')->willReturn('https://gitlab.com');
        $commenter->expects(static::once())->method('postNote')->willReturn('https://gitlab.com');

        $gitlab = new Gitlab($client, $commenter);
        $gitlab->load('test', '1');

        static::assertSame('https://gitlab.com', $gitlab->post('Test', new Config()));
        static::assertSame('https://gitlab.com', $gitlab->post('Test', (new Config())->useThreadOn(Config::REPORT_LEVEL_FAILURE)));
    }

    public function testRemove(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $commenter = $this->createMock(GitlabCommenter::class);
        $commenter->expects(static::once())->method('removeThread');
        $commenter->expects(static::once())->method('removeNote');

        $gitlab = new Gitlab($client, $commenter);
        $gitlab->load('test', '1');
        $gitlab->removePost(new Config());
        $gitlab->removePost((new Config())->useThreadOn(Config::REPORT_LEVEL_FAILURE));
    }

    public function testLabels(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $gitlab = new Gitlab($client, $this->createMock(GitlabCommenter::class));
        $gitlab->load('test', '1');

        $gitlab->removeLabels('Test');
        static::assertSame([], $gitlab->pullRequest->labels);

        $gitlab->addLabels('Test');
        static::assertSame(['Test'], $gitlab->pullRequest->labels);

        static::assertSame(3, $mockHttpClient->getRequestsCount());
    }

    public function testHasDangerComment(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));
        $commenter = $this->createMock(GitlabCommenter::class);
        $commenter->method('getRelevantNoteIds')->willReturn([1, 2, 3]);

        $gitlab = new Gitlab($client, $commenter);
        $gitlab->load('test', '1');

        static::assertTrue($gitlab->hasDangerMessage());
    }

    public function testFetchingHeadFile(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{"content": "dGVzdA=="}', ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $gitlab = new Gitlab($client, $this->createMock(GitlabCommenter::class));
        $gitlab->load('test', '1');

        $file = $gitlab->pullRequest->getFileContent('composer.json');
        static::assertSame('test', $file);
    }

    public function testFetchingHeadFileFails(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse((string) file_get_contents(__DIR__ . '/payloads/mr.json'), ['http_response' => 200, 'response_headers' => ['content-type' => 'application/json']]),
            new MockResponse('{}', ['http_response' => 404, 'response_headers' => ['content-type' => 'application/json']]),
        ]);

        $client = Client::createWithHttpClient(new Psr18Client($mockHttpClient));

        $gitlab = new Gitlab($client, $this->createMock(GitlabCommenter::class));
        $gitlab->load('test', '1');

        static::expectException(CouldNotGetFileContentException::class);
        static::expectExceptionMessage('Could not get content of file composer.json');

        $gitlab->pullRequest->getFileContent('composer.json');
    }
}
