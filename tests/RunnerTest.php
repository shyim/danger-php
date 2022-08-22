<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Config;
use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Runner;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RunnerTest extends TestCase
{
    public function testRunner(): void
    {
        $runner = new Runner();

        $ruleExecuted = false;
        $afterExecuted = false;

        $config = new Config();
        $config->useRule(function () use (&$ruleExecuted, &$afterExecuted): void {
            static::assertFalse($afterExecuted); /** @phpstan-ignore-line */
            $ruleExecuted = true;
        });

        $config->after(function () use (&$ruleExecuted, &$afterExecuted): void {
            static::assertTrue($ruleExecuted);
            $afterExecuted = true;
        });

        $config->useThreadOn(Config::REPORT_LEVEL_FAILURE);
        $runner->run($config, new Context($this->createMock(Github::class)));

        static::assertTrue($ruleExecuted);
        static::assertTrue($afterExecuted);
    }
}
