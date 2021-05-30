<?php
declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\GithubCommand;
use Danger\ConfigLoader;
use Danger\Platform\Github\Github;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 */
class GithubCommandTest extends TestCase
{
    public function testInvalidUrl(): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('The given url must be a valid Github URL');

        $cmd->run(new ArgvInput(['danger', 'https://github.com']), new NullOutput());
    }

    public function testInvalidConfig(): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Invalid config option given');

        $input = new ArgvInput(['danger', 'https://github.com']);
        $input->bind($cmd->getDefinition());
        $input->setOption('config', []);

        $cmd->execute($input, new NullOutput());
    }

    public function testInvalidPr(): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('The PR links needs to be a string');

        $input = new ArgvInput(['danger', 'https://github.com']);
        $input->bind($cmd->getDefinition());
        $input->setArgument('pr', []);

        $cmd->execute($input, new NullOutput());
    }

    public function testValidUrlWithoutIssues(): void
    {
        $github = $this->createMock(Github::class);
        $github
            ->expects(static::once())
            ->method('load')
            ->with('shyim/danger-php', '1')
        ;

        $cmd = new GithubCommand($github, new ConfigLoader(), new Runner());

        static::assertSame(0, $cmd->run(new ArgvInput(['danger', 'https://github.com/shyim/danger-php/pull/1', '--config=' . dirname(__DIR__) . '/configs/empty.php']), new NullOutput()));
    }

    public function testValidUrlWithIssues(): void
    {
        $github = $this->createMock(Github::class);
        $github
            ->expects(static::once())
            ->method('load')
            ->with('shyim/danger-php', '1')
        ;

        $cmd = new GithubCommand($github, new ConfigLoader(), new Runner());

        static::assertSame(-1, $cmd->run(new ArgvInput(['danger', 'https://github.com/shyim/danger-php/pull/1', '--config=' . dirname(__DIR__) . '/configs/all.php']), new NullOutput()));
    }
}
