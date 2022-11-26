<?php declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\LocalCommand;
use Danger\ConfigLoader;
use Danger\Platform\Local\LocalPlatform;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \Danger\Command\LocalCommand
 *
 * @internal
 */
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
        $cmd = new LocalCommand(new ConfigLoader(), new Runner(), $this->createMock(LocalPlatform::class));

        $input = new ArgvInput(['danger', '--config=' . \dirname(__DIR__) . '/configs/empty.php']);
        $input->bind($cmd->getDefinition());

        $output = new BufferedOutput();
        $returnCode = $cmd->execute($input, $output);

        static::assertStringContainsString('PR looks good', $output->fetch());
        static::assertSame(0, $returnCode);
    }
}
