<?php
declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\GithubCommand;
use Danger\ConfigLoader;
use Danger\Platform\Github\Github;
use Danger\Runner;
use function dirname;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class GithubCommandTest extends TestCase
{
    /**
     * @return array<int, string>[]
     */
    public function invalidUrls(): array
    {
        return [
            ['https://github.com'],
            ['testhttps://github.com'],
            ['testhttps://github.com/shyim/danger-php'],
            ['https://gitlab.com'],
        ];
    }

    /**
     * @dataProvider invalidUrls
     */
    public function testInvalidUrl(string $url): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The given url must be a valid Github URL');

        $cmd->run(new ArgvInput(['danger', $url]), new NullOutput());
    }

    public function testInvalidConfig(): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid config option given');

        $input = new ArgvInput(['danger', 'https://github.com']);
        $input->bind($cmd->getDefinition());
        $input->setOption('config', []);

        $cmd->execute($input, new NullOutput());
    }

    public function testInvalidPr(): void
    {
        $cmd = new GithubCommand($this->createMock(Github::class), new ConfigLoader(), new Runner());

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The PR links needs to be a string');

        $tester = new CommandTester($cmd);
        $tester->execute(['pr' => []]);
    }

    public function testValidUrlWithoutIssues(): void
    {
        $github = $this->createMock(Github::class);
        $github
            ->expects(static::once())
            ->method('load')
            ->with('shyim/danger-php', '1')
        ;

        $tester = new CommandTester(new GithubCommand($github, new ConfigLoader(), new Runner()));

        $exitCode = $tester->execute(['pr' => 'https://github.com/shyim/danger-php/pull/1', '--config' => dirname(__DIR__) . '/configs/empty.php']);
        static::assertSame(Command::SUCCESS, $exitCode);
        static::assertStringContainsString('PR looks good!', $tester->getDisplay());
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
        $tester = new CommandTester($cmd);
        $exitCode = $tester->execute([
            'pr' => 'https://github.com/shyim/danger-php/pull/1',
            '--config' => dirname(__DIR__) . '/configs/all.php',
        ]);

        static::assertSame(Command::FAILURE, $exitCode);
        static::assertStringContainsString('Failures', $tester->getDisplay());
        static::assertStringContainsString('Warnings', $tester->getDisplay());
        static::assertStringContainsString('Notices', $tester->getDisplay());
        static::assertStringContainsString('A Failure', $tester->getDisplay());
        static::assertStringContainsString('A Warning', $tester->getDisplay());
        static::assertStringContainsString('A Notice', $tester->getDisplay());
    }
}
