<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Config;
use Danger\Context;
use Danger\Platform\AbstractPlatform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $config = new Config();

        $config->useRule(static function (): void {});
        static::assertCount(1, $config->getRules());

        static::assertFalse($config->isThreadEnabled());
        $config->useThreadOnFails(); /** @phpstan-ignore-line */
        static::assertTrue($config->isThreadEnabled());

        $config->useThreadOnFails(false); /** @phpstan-ignore-line */
        $config->useThreadOn(Config::REPORT_LEVEL_WARNING);
        static::assertEquals(Config::REPORT_LEVEL_WARNING, $config->getUseThreadOn());

        $config->useThreadOn(Config::REPORT_LEVEL_WARNING);
        static::assertEquals(Config::REPORT_LEVEL_WARNING, $config->getUseThreadOn());

        $config->useThreadOn(Config::REPORT_LEVEL_NOTICE);
        static::assertEquals(Config::REPORT_LEVEL_NOTICE, $config->getUseThreadOn());

        $config->useThreadOn(Config::REPORT_LEVEL_NONE);
        static::assertEquals(Config::REPORT_LEVEL_NONE, $config->getUseThreadOn());

        static::assertNull($config->getGithubCommentProxy());
        $config->useGithubCommentProxy('http://localhost');
        static::assertSame('http://localhost', $config->getGithubCommentProxy());

        static::assertSame(Config::UPDATE_COMMENT_MODE_UPDATE, $config->getUpdateCommentMode());
        $config->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE);
        static::assertSame(Config::UPDATE_COMMENT_MODE_REPLACE, $config->getUpdateCommentMode());

        $config->after(static function (): void {});
        static::assertCount(1, $config->getAfterHooks());
    }

    public function testGetReportLevelNotice(): void
    {
        $config = new Config();

        $context = new Context($this->createMock(AbstractPlatform::class));
        $context->notice('test');

        static::assertSame(Config::REPORT_LEVEL_NOTICE, $config->getReportLevel($context));
    }

    public function testGetReportLevelNone(): void
    {
        $config = new Config();

        $context = new Context($this->createMock(AbstractPlatform::class));

        static::assertSame(Config::REPORT_LEVEL_NONE, $config->getReportLevel($context));
    }
}
