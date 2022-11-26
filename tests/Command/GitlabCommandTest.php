<?php
declare(strict_types=1);

namespace Danger\Tests\Command;

use Danger\Command\GitlabCommand;
use Danger\ConfigLoader;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class GitlabCommandTest extends TestCase
{
    public function testRunWithoutToken(): void
    {
        if (isset($_SERVER['DANGER_GITLAB_TOKEN'])) {
            unset($_SERVER['DANGER_GITLAB_TOKEN']);
        }

        $cmd = new GitlabCommand($this->createMock(Gitlab::class), new ConfigLoader(), new Runner());
        $output = new BufferedOutput();
        $cmd->run(new ArgvInput(['danger', 'test', '1']), $output);

        static::assertStringContainsString('DANGER_GITLAB_TOKEN ', $output->fetch());
    }

    public function testInvalidConfig(): void
    {
        $_SERVER['DANGER_GITLAB_TOKEN'] = '1';

        $cmd = new GitlabCommand($this->createMock(Gitlab::class), new ConfigLoader(), new Runner());

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid config option given');

        $input = new ArgvInput(['danger', 'https://github.com']);
        $input->bind($cmd->getDefinition());
        $input->setArgument('projectIdentifier', 'test');
        $input->setArgument('mrID', 'test');
        $input->setOption('config', []);

        $cmd->execute($input, new NullOutput());

        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }

    public function testValid(): void
    {
        $_SERVER['DANGER_GITLAB_TOKEN'] = '1';

        $gitlab = $this->createMock(Gitlab::class);
        $gitlab
            ->expects(static::once())
            ->method('load')
            ->with('test', '1')
        ;

        $cmd = new GitlabCommand($gitlab, new ConfigLoader(), new Runner());

        $output = new BufferedOutput();
        $returnCode = $cmd->run(new ArgvInput(['danger', 'test', '1', '--config=' . \dirname(__DIR__) . '/configs/empty.php']), $output);

        $text = $output->fetch();

        static::assertStringContainsString('PR looks good', $text);
        static::assertSame(0, $returnCode);

        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }

    public function testValidWithErrors(): void
    {
        $_SERVER['DANGER_GITLAB_TOKEN'] = '1';

        $gitlab = $this->createMock(Gitlab::class);
        $gitlab
            ->expects(static::once())
            ->method('load')
            ->with('test', '1')
        ;

        $cmd = new GitlabCommand($gitlab, new ConfigLoader(), new Runner());

        $tester = new CommandTester($cmd);
        $exitCode = $tester->execute([
            'mrID' => '1',
            'projectIdentifier' => 'test',
            '--config' => \dirname(__DIR__) . '/configs/all.php',
        ]);

        static::assertSame(Command::FAILURE, $exitCode);
        static::assertStringContainsString('Failures', $tester->getDisplay());
        static::assertStringContainsString('Warnings', $tester->getDisplay());
        static::assertStringContainsString('Notices', $tester->getDisplay());
        static::assertStringContainsString('A Failure', $tester->getDisplay());
        static::assertStringContainsString('A Warning', $tester->getDisplay());
        static::assertStringContainsString('A Notice', $tester->getDisplay());

        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }
}
