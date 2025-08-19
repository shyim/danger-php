<?php declare(strict_types=1);

namespace Danger\Tests\Platform\Local;

use Danger\Config;
use Danger\Platform\Local\LocalPlatform;
use Danger\Struct\Local\LocalPullRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
#[CoversClass(LocalPlatform::class)]
class LocalPlatformTest extends TestCase
{
    public function testPlatform(): void
    {
        $platform = new LocalPlatform();

        static::assertFalse($platform->hasDangerMessage());
        $platform->removePost(new Config());
        static::assertFalse($platform->hasDangerMessage());

        $platform->post('test', new Config());
        static::assertTrue($platform->hasDangerMessage());
    }

    public function testLoad(): void
    {
        $tmpDir = sys_get_temp_dir() . '/' . uniqid('local', true);

        mkdir($tmpDir);
        file_put_contents($tmpDir . '/a.txt', 'a');

        (new Process(['git', 'init'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'commit.gpgsign', 'false'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.name', 'PHPUnit'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.email', 'unit@php.com'], $tmpDir))->mustRun();
        (new Process(['git', 'branch', '-m', 'main'], $tmpDir))->mustRun();
        (new Process(['git', 'add', 'a.txt'], $tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'initial'], $tmpDir))->mustRun();

        $platform = new LocalPlatform();
        $platform->load($tmpDir, 'main|main');

        static::assertInstanceOf(LocalPullRequest::class, $platform->pullRequest);
        static::assertSame('empty', $platform->pullRequest->title);
    }
}
