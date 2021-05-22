<?php
declare(strict_types=1);

namespace Danger\Command;

use Danger\ConfigLoader;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
class GitlabCommandTest extends TestCase
{
    public function testRunWithoutToken(): void
    {
        $cmd = new GitlabCommand($this->createMock(Gitlab::class), new ConfigLoader(), new Runner());
        $output = new BufferedOutput();
        $cmd->run(new ArgvInput(['danger', 'test', '1']), $output);

        static::assertStringContainsString('You need the environment variable DANGER_GITLAB_TOKEN with an Gitlab API Token to use this command', $output->fetch());
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
        $returnCode = $cmd->run(new ArgvInput(['danger', 'test', '1', '--config=' . dirname(__DIR__) . '/configs/empty.php']), $output);

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

        $output = new BufferedOutput();
        $returnCode = $cmd->run(new ArgvInput(['danger', 'test', '1', '--config=' . dirname(__DIR__) . '/configs/all.php']), $output);

        $text = $output->fetch();

        static::assertStringContainsString('Failures', $text);
        static::assertStringContainsString('Warnings', $text);
        static::assertStringContainsString('Notices', $text);
        static::assertSame(-1, $returnCode);

        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }
}
