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
        static::assertSame(0, $fileA->additions);
        static::assertSame(1, $fileA->deletions);
        static::assertSame(1, $fileA->changes);

        $fileB = $files->get('b.txt');

        static::assertNotNull($fileB);

        static::assertSame('b2', $fileB->getContent());
        static::assertSame(File::STATUS_ADDED, $fileB->status);
        static::assertSame(1, $fileB->additions);
        static::assertSame(0, $fileB->deletions);
        static::assertSame(1, $fileB->changes);
        static::assertNotEmpty($fileB->patch);

        $fileC = $files->get('c.txt');

        static::assertNotNull($fileC);

        static::assertSame('c', $fileC->getContent());
        static::assertSame(File::STATUS_ADDED, $fileC->status);
        static::assertSame(1, $fileC->additions);
        static::assertSame(0, $fileC->deletions);
        static::assertSame(1, $fileC->changes);

        $fileModified = $files->get('modified.txt');
        static::assertNotNull($fileModified);
        static::assertSame(File::STATUS_MODIFIED, $fileModified->status);
        static::assertSame(1, $fileModified->additions);
        static::assertSame(1, $fileModified->deletions);
        static::assertSame(2, $fileModified->changes);
        static::assertNotEmpty($fileModified->patch);
    }

    public function testDiffStatsForSingleAddedFile(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature', 'main');

        $files = $pr->getFiles();

        static::assertCount(1, $files);

        $fileB = $files->get('b.txt');
        static::assertNotNull($fileB);

        static::assertSame(File::STATUS_ADDED, $fileB->status);
        static::assertSame(1, $fileB->additions);
        static::assertSame(0, $fileB->deletions);
        static::assertSame(1, $fileB->changes);
        static::assertNotEmpty($fileB->patch);
    }

    public function testDiffStatsForMultilineChanges(): void
    {
        (new Process(['git', 'checkout', '-b', 'multiline'], $this->tmpDir))->mustRun();

        file_put_contents($this->tmpDir . '/multi.txt', "line1\nline2\nline3\nline4\nline5\n");
        (new Process(['git', 'add', 'multi.txt'], $this->tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'add multiline file'], $this->tmpDir))->mustRun();

        (new Process(['git', 'checkout', '-b', 'multiline-edit'], $this->tmpDir))->mustRun();

        file_put_contents($this->tmpDir . '/multi.txt', "line1\nchanged2\nline3\nchanged4\nline5\nnewline6\n");
        (new Process(['git', 'add', 'multi.txt'], $this->tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'edit multiline file'], $this->tmpDir))->mustRun();

        $pr = new LocalPullRequest($this->tmpDir, 'multiline-edit', 'multiline');

        $files = $pr->getFiles();

        static::assertCount(1, $files);

        $file = $files->get('multi.txt');
        static::assertNotNull($file);

        static::assertSame(File::STATUS_MODIFIED, $file->status);
        static::assertSame(3, $file->additions);
        static::assertSame(2, $file->deletions);
        static::assertSame(5, $file->changes);

        static::assertStringContainsString('+changed2', $file->patch);
        static::assertStringContainsString('-line2', $file->patch);
        static::assertStringContainsString('+newline6', $file->patch);
    }

    public function testPatchContentForDeletedFile(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature2', 'main');

        $files = $pr->getFiles();

        $fileA = $files->get('a.txt');
        static::assertNotNull($fileA);
        static::assertSame(File::STATUS_REMOVED, $fileA->status);
        static::assertStringContainsString('-a', $fileA->patch);
    }

    public function testPatchContentForModifiedFile(): void
    {
        $pr = new LocalPullRequest($this->tmpDir, 'feature2', 'main');

        $files = $pr->getFiles();

        $fileModified = $files->get('modified.txt');
        static::assertNotNull($fileModified);

        static::assertStringContainsString('-a', $fileModified->patch);
        static::assertStringContainsString('+b', $fileModified->patch);
        static::assertStringContainsString('@@', $fileModified->patch);
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
