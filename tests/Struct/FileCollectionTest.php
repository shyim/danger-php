<?php
declare(strict_types=1);

namespace Danger\Tests\Struct;

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
        $f = new File('');
        $f->name = 'CHANGELOG.md';
        $f->status = File::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertCount(1, $c->filterStatus(File::STATUS_ADDED));

        $f->status = File::STATUS_MODIFIED;

        static::assertCount(1, $c->filterStatus(File::STATUS_MODIFIED));

        $f->status = File::STATUS_REMOVED;

        static::assertCount(1, $c->filterStatus(File::STATUS_REMOVED));
    }

    public function testClear(): void
    {
        $f = new File('');
        $f->name = 'CHANGELOG.md';
        $f->status = File::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertCount(1, $c);
        $c->clear();
        static::assertCount(0, $c);
    }

    public function testGet(): void
    {
        $f = new File('');
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
        $f = new File('');
        $c = new FileCollection([$f]);

        static::assertCount(0, $c->slice(1));
        static::assertCount(1, $c->slice(0));
    }

    public function testReduce(): void
    {
        $f = new File('');
        $c = new FileCollection([$f]);

        $bool = $c->reduce(static function (): bool {
            return true;
        });

        static::assertTrue($bool);
    }

    public function testSort(): void
    {
        $f1 = new File('');
        $f1->name = 'A';

        $f2 = new File('');
        $f2->name = 'Z';

        $c = new FileCollection([$f2, $f1]);

        $c->sort(static function (File $a, File $b): int {
            return $a->name <=> $b->name;
        });

        $file = $c->first();
        static::assertInstanceOf(File::class, $file);

        static::assertSame('A', $file->name);
    }

    public function testFilesMatching(): void
    {
        $f1 = new File('');
        $f1->name = 'README.md';

        $f2 = new File('');
        $f2->name = 'changelogs/_unreleased/some-file.md';

        $f3 = new File('');
        $f3->name = 'src/Test.php';

        $c = new FileCollection([$f1, $f2, $f3]);

        $newCollection = $c->matches('changelogs/**/*.md');

        static::assertCount(1, $newCollection);
        $item = $newCollection->first();
        static::assertInstanceOf(File::class, $item);
        static::assertSame('changelogs/_unreleased/some-file.md', $item->name);
    }

    public function testFilesMatchingContent(): void
    {
        $f1 = new File('./tests/fixtures/README.md');
        $f1->name = 'tests/fixtures/README.md';

        $f2 = new File('./tests/fixtures/SqlHeredocFixture.php');
        $f2->name = 'tests/fixtures/SqlHeredocFixture.php';

        $f3 = new File('./tests/fixtures/SqlNowdocFixture.php');
        $f3->name = 'tests/fixtures/SqlNowdocFixture.php';

        $c = new FileCollection([$f1, $f2, $f3]);

        $newCollection = $c->matchesContent('/<<<SQL/');

        static::assertCount(1, $newCollection);
        $item = $newCollection->first();
        static::assertInstanceOf(File::class, $item);
        static::assertSame('tests/fixtures/SqlHeredocFixture.php', $item->name);
    }
}
