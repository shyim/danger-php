<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Config;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $config = new Config();

        $config->useRule(static function (): void {});
        static::assertCount(1, $config->getRules());

        static::assertFalse($config->isThreadEnabled());
        $config->useThreadOnFails();
        static::assertTrue($config->isThreadEnabled());

        static::assertNull($config->getGithubCommentProxy());
        $config->useGithubCommentProxy('http://localhost');
        static::assertSame('http://localhost', $config->getGithubCommentProxy());

        static::assertSame(Config::UPDATE_COMMENT_MODE_UPDATE, $config->getUpdateCommentMode());
        $config->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE);
        static::assertSame(Config::UPDATE_COMMENT_MODE_REPLACE, $config->getUpdateCommentMode());

        $config->after(static function (): void {});
        static::assertCount(1, $config->getAfterHooks());
    }
}
