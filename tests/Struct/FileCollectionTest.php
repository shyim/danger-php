<?php
declare(strict_types=1);

namespace Danger\Tests\Struct;

use Danger\Struct\File as FileAlias;
use Danger\Struct\FileCollection;
use Danger\Struct\Github\File;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FileCollectionTest extends TestCase
{
    public function testFileExistsHelper(): void
    {
        $f = new FakeFile('');
        $f->name = 'CHANGELOG.md';
        $f->status = FileAlias::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertCount(1, $c->filterStatus(FileAlias::STATUS_ADDED));

        $f->status = FileAlias::STATUS_MODIFIED;

        static::assertCount(1, $c->filterStatus(FileAlias::STATUS_MODIFIED));

        $f->status = FileAlias::STATUS_REMOVED;

        static::assertCount(1, $c->filterStatus(FileAlias::STATUS_REMOVED));
    }

    public function testClear(): void
    {
        $f = new FakeFile('');
        $f->name = 'CHANGELOG.md';
        $f->status = FileAlias::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertCount(1, $c);
        $c->clear();
        static::assertCount(0, $c);
    }

    public function testGet(): void
    {
        $f = new FakeFile('');
        $c = new FileCollection([$f]);
        static::assertSame($f, $c->get('0'));
        static::assertNull($c->get('1'));
        static::assertCount(1, $c->getKeys());
        static::assertCount(1, $c->getElements());
        $c->remove('0');
        static::assertCount(0, $c->getElements());
    }

    public function testSlice(): void
    {
        $f = new FakeFile('');
        $c = new FileCollection([$f]);

        static::assertCount(0, $c->slice(1));
        static::assertCount(1, $c->slice(0));
    }

    public function testReduce(): void
    {
        $f = new FakeFile('');
        $c = new FileCollection([$f]);

        $bool = $c->reduce(static fn (): bool => true);

        static::assertTrue($bool);
    }

    public function testSort(): void
    {
        $f1 = new FakeFile('');
        $f1->name = 'A';

        $f2 = new FakeFile('');
        $f2->name = 'Z';

        $c = new FileCollection([$f2, $f1]);

        $c->sort(static fn (FakeFile $a, FakeFile $b): int => $a->name <=> $b->name);

        $file = $c->first();
        static::assertInstanceOf(FakeFile::class, $file);

        static::assertSame('A', $file->name);
    }

    public function testFilesMatching(): void
    {
        $f1 = new FakeFile('');
        $f1->name = 'README.md';

        $f2 = new FakeFile('');
        $f2->name = 'changelogs/_unreleased/some-file.md';

        $f3 = new FakeFile('');
        $f3->name = 'src/Test.php';

        $c = new FileCollection([$f1, $f2, $f3]);

        $newCollection = $c->matches('changelogs/**/*.md');

        static::assertCount(1, $newCollection);
        $item = $newCollection->first();
        static::assertInstanceOf(FakeFile::class, $item);
        static::assertSame('changelogs/_unreleased/some-file.md', $item->name);
    }

    public function testFilesMatchingContent(): void
    {
        $f1 = new FakeFile('./tests/fixtures/README.md');
        $f1->name = 'tests/fixtures/README.md';

        $f2 = new FakeFile('./tests/fixtures/SqlHeredocFixture.php');
        $f2->name = 'tests/fixtures/SqlHeredocFixture.php';

        $f3 = new FakeFile('./tests/fixtures/SqlNowdocFixture.php');
        $f3->name = 'tests/fixtures/SqlNowdocFixture.php';

        $c = new FileCollection([$f1, $f2, $f3]);

        $newCollection = $c->matchesContent('/<<<SQL/');

        static::assertCount(1, $newCollection);
        $item = $newCollection->first();
        static::assertInstanceOf(FakeFile::class, $item);
        static::assertSame('tests/fixtures/SqlHeredocFixture.php', $item->name);
    }

    public function testMap(): void
    {
        $f1 = new FakeFile('');
        $f1->name = 'A';

        $c = new FileCollection([$f1]);
        $list = $c->fmap(fn (FakeFile $file) => $file->name);

        static::assertSame(['A'], $list);
    }
}

/**
 * @internal
 */
class FakeFile extends FileAlias
{
    public function __construct(private string $fileName)
    {
    }

    public function getContent(): string
    {
        return (string) file_get_contents($this->fileName);
    }
}
