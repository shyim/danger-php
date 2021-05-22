<?php
declare(strict_types=1);

namespace Danger\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class InitCommandTest extends TestCase
{
    public function testCreatesFile(): void
    {
        $currentDir = getcwd();
        chdir('/tmp');

        if (file_exists('/tmp/.danger.php')) {
            unlink('/tmp/.danger.php');
        }

        $cmd = new InitCommand();
        $output = new BufferedOutput();
        static::assertSame(0, $cmd->run(new ArgvInput([]), $output));

        static::assertFileExists('/tmp/.danger.php');
        static::assertStringContainsString('Created', $output->fetch());
        unlink('/tmp/.danger.php');
        chdir($currentDir);
    }

    public function testOverwriteFile(): void
    {
        $currentDir = getcwd();
        chdir('/tmp');
        touch('/tmp/.danger.php');

        $cmd = new InitCommand();
        $tester = new CommandTester($cmd);
        $tester->setInputs(['yes']);
        static::assertSame(0, $tester->execute([], ['interactive' => true]));

        static::assertFileExists('/tmp/.danger.php');
        static::assertStringContainsString('Created', $tester->getDisplay());
        unlink('/tmp/.danger.php');
        chdir($currentDir);
    }

    public function testNotOverwriteFile(): void
    {
        $currentDir = getcwd();
        chdir('/tmp');
        touch('/tmp/.danger.php');

        $cmd = new InitCommand();
        $tester = new CommandTester($cmd);
        $tester->setInputs(['no']);
        static::assertSame(0, $tester->execute([], ['interactive' => true]));

        static::assertFileExists('/tmp/.danger.php');
        static::assertStringNotContainsString('Created', $tester->getDisplay());
        unlink('/tmp/.danger.php');
        chdir($currentDir);
    }
}
