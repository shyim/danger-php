<?php declare(strict_types=1);

namespace Danger\Tests\Struct\Local;

use Danger\Exception\CouldNotGetFileContentException;
use Danger\Struct\File;
use Danger\Struct\Local\LocalPullRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
class LocalPullRequestTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/' . uniqid('local', true);

        mkdir($this->tmpDir);
        file_put_contents($this->tmpDir . '/a.txt', 'a');
        file_put_contents($this->tmpDir . '/b.txt', 'b');
        file_put_contents($this->tmpDir . '/c.txt', 'c');
        file_put_contents($this->tmpDir . '/modified.txt', 'a');

        (new Process(['git', 'init'], $this->tmpDir))->mustRun();
        (new Process(['git', 'config', 'commit.gpgsign', 'false'], $this->tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.name', 'PHPUnit'], $this->tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.email', 'unit@php.com'], $this->tmpDir))->mustRun();
        (new Process(['git', 'branch', '-m', 'main'], $this->tmpDir))->mustRun();
        (new Process(['git', 'add', 'a.txt'], $this->tmpDir))->mustRun();
        (new Process(['git', 'add', 'modified.txt'], $this->tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'initial'], $this->tmpDir))->mustRun();

        (new Process(['git', 'checkout', '-b', 'feature'], $this->tmpDir))->mustRun();
        (new Process(['git', 'add', 'b.txt'], $this->tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'feature'], $this->tmpDir))->mustRun();

        (new Process(['git', 'checkout', '-b', 'feature2'], $this->tmpDir))->mustRun();
        (new Process(['git', 'rm', 'a.txt'], $this->tmpDir))->mustRun();
        file_put_contents($this->tmpDir . '/b.txt', 'b2');
        file_put_contents($this->tmpDir . '/modified.txt', 'b');
        (new Process(['git', 'add', 'b.txt', 'c.txt', 'modified.txt'], $this->tmpDir))->mustRun();

        (new Process(['git', 'commit', '-m', 'all modes'], $this->tmpDir))->mustRun();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem())->remove($this->tmpDir);
    }

    public function testCommits(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature', 'main');
        static::assertSame('feature', $pr->title);

        $commits = $pr->getCommits();

        static::assertCount(1, $commits);

        $commit = $commits->first();
        static::assertNotNull($commit);

        static::assertSame('feature', $commit->message);
        static::assertSame('PHPUnit', $commit->author);
        static::assertSame('unit@php.com', $commit->authorEmail);
        static::assertEqualsWithDelta(time(), $commit->createdAt->getTimestamp(), 1);
    }

    public function testEmptyCommits(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'main', 'main');
        static::assertSame('empty', $pr->title);

        static::assertCount(0, $pr->getCommits());
        static::assertCount(0, $pr->getFiles());
        static::assertCount(0, $pr->getComments());
    }

    public function testGetFiles(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature2', 'main');

        $files = $pr->getFiles();

        $files2 = $pr->getFiles();

        static::assertSame($files, $files2);

        $fileA = $files->get('a.txt');

        static::assertNotNull($fileA);

        static::assertSame('a.txt', $fileA->name);
        static::assertSame(File::STATUS_REMOVED, $fileA->status);

        $fileB = $files->get('b.txt');

        static::assertNotNull($fileB);

        static::assertSame('b2', $fileB->getContent());
        static::assertSame(File::STATUS_ADDED, $fileB->status);

        $fileC = $files->get('c.txt');

        static::assertNotNull($fileC);

        static::assertSame('c', $fileC->getContent());
        static::assertSame(File::STATUS_ADDED, $fileC->status);

        $fileModified = $files->get('modified.txt');
        static::assertNotNull($fileModified);
        static::assertSame(File::STATUS_MODIFIED, $fileModified->status);
    }

    public function testGetSingleFile(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature2', 'main');
        static::assertSame('', $pr->getFile('a.txt')->getContent());
    }

    public function testGetHeadFile(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature2', 'main');

        $file = $pr->getFileContent('c.txt');
        static::assertSame('c', $file);

        static::expectException(CouldNotGetFileContentException::class);

        $pr->getFileContent('foo.txt');
    }
}
