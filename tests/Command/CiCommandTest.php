<?php
declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\CiCommand;
use Danger\ConfigLoader;
use Danger\Platform\Github\Github;
use Danger\Platform\PlatformDetector;
use Danger\Renderer\HTMLRenderer;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 */
class CiCommandTest extends TestCase
{
    public function testValid(): void
    {
        $platform = $this->createMock(Github::class);

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);

        $output = new BufferedOutput();

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());
        $returnCode = $cmd->run(new ArgvInput(['danger', '--config=' . dirname(__DIR__) . '/configs/empty.php']), $output);

        static::assertSame(0, $returnCode);
        static::assertStringContainsString('Looks good!', $output->fetch());
    }

    public function testNotValid(): void
    {
        $platform = $this->createMock(Github::class);
        $platform->method('post')->willReturn('http://danger.local/test');

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);
        $output = new BufferedOutput();

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());
        $returnCode = $cmd->run(new ArgvInput(['danger', '--config=' . dirname(__DIR__) . '/configs/all.php']), $output);

        static::assertSame(-1, $returnCode);
        static::assertStringContainsString('The comment has been created at http://danger.local/test', $output->fetch());
    }

    public function testNotValidWarning(): void
    {
        $platform = $this->createMock(Github::class);
        $platform->method('post')->willReturn('http://danger.local/test');

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);
        $output = new BufferedOutput();

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());
        $returnCode = $cmd->run(new ArgvInput(['danger', '--config=' . dirname(__DIR__) . '/configs/warning.php']), $output);

        static::assertSame(0, $returnCode);
        static::assertStringContainsString('The comment has been created at http://danger.local/test', $output->fetch());
    }

    public function testInvalidConfig(): void
    {
        $platform = $this->createMock(Github::class);
        $platform->method('post')->willReturn('http://danger.local/test');

        $detector = $this->createMock(PlatformDetector::class);
        $detector->method('detect')->willReturn($platform);

        $cmd = new CiCommand($detector, new ConfigLoader(), new Runner(), new HTMLRenderer());

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Invalid config option given');

        $input = new ArgvInput([]);
        $input->bind($cmd->getDefinition());
        $input->setOption('config', 1);

        $cmd->execute($input, new NullOutput());
    }
}
