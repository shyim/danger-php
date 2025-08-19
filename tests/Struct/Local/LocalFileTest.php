<?php declare(strict_types=1);

namespace Danger\Tests\Struct\Local;

use Danger\Struct\Local\LocalFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LocalFile::class)]
class LocalFileTest extends TestCase
{
    public function testGetContent(): void
    {
        $file = new LocalFile(__FILE__);
        static::assertSame((string) file_get_contents(__FILE__), $file->getContent());
    }
}
