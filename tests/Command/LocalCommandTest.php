<?php declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\LocalCommand;
use Danger\ConfigLoader;
use Danger\Platform\Local\LocalPlatform;
use Danger\Runner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
#[CoversClass(LocalCommand::class)]
class LocalCommandTest extends TestCase
{
    public function testInvalidConfig(): void
    {
        $cmd = new LocalCommand(new ConfigLoader(), new Runner(), $this->createMock(LocalPlatform::class));

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid config option given');

        $input = new ArgvInput(['danger']);
        $input->bind($cmd->getDefinition());
        $input->setOption('config', []);

        $cmd->execute($input, new NullOutput());
    }

    public function testInvalidRoot(): void
    {
        $cmd = new LocalCommand(new ConfigLoader(), new Runner(), $this->createMock(LocalPlatform::class));

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid root option given');

        $input = new ArgvInput(['danger']);
        $input->bind($cmd->getDefinition());
        $input->setOption('root', []);

        $cmd->execute($input, new NullOutput());
    }

    public function testCommand(): void
    {
        $tmpDir = sys_get_temp_dir() . '/' . uniqid('local', true);
        $tmpDirTarget = sys_get_temp_dir() . '/' . uniqid('local', true);

        mkdir($tmpDir);
        mkdir($tmpDirTarget);
        file_put_contents($tmpDir . '/a.txt', 'a');

        (new Process(['git', 'init', '--bare', '-b', 'main'], $tmpDirTarget))->mustRun();

        (new Process(['git', 'init', '-b', 'main'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'commit.gpgsign', 'false'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.name', 'PHPUnit'], $tmpDir))->mustRun();
        (new Process(['git', 'config', 'user.email', 'unit@php.com'], $tmpDir))->mustRun();
        (new Process(['git', 'branch', '-m', 'main'], $tmpDir))->mustRun();
        (new Process(['git', 'add', 'a.txt'], $tmpDir))->mustRun();
        (new Process(['git', 'commit', '-m', 'initial'], $tmpDir))->mustRun();
        (new Process(['git', 'remote', 'add', 'origin', 'file://' . $tmpDirTarget], $tmpDir))->mustRun();
        (new Process(['git', 'push', '-u', 'origin', 'main'], $tmpDir))->mustRun();

        (new Filesystem())->remove($tmpDir);
        (new Process(['git', 'clone', 'file://' . $tmpDirTarget, $tmpDir]))->mustRun();

        $cmd = new LocalCommand(new ConfigLoader(), new Runner(), $this->createMock(LocalPlatform::class));

        $input = new ArgvInput(['danger', '--config=' . \dirname(__DIR__) . '/configs/empty.php', '--root=' . $tmpDir]);
        $input->bind($cmd->getDefinition());

        $output = new BufferedOutput();
        $returnCode = $cmd->execute($input, $output);

        static::assertStringContainsString('PR looks good', $output->fetch());
        static::assertSame(0, $returnCode);

        (new Filesystem())->remove($tmpDir);
        (new Filesystem())->remove($tmpDirTarget);
    }
}
