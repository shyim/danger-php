<?php
declare(strict_types=1);

namespace Danger\Tests\Platform;

use Danger\Exception\UnsupportedCIException;
use Danger\Platform\Github\Github;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Platform\PlatformDetector;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PlatformDetectorTest extends TestCase
{
    public function testUnknown(): void
    {
        $detector = new PlatformDetector($this->createMock(Github::class), $this->createMock(Gitlab::class));
        static::expectException(UnsupportedCIException::class);
        static::expectDeprecationMessage('Could not detect CI Platform');
        $detector->detect();
    }

    public function testGithub(): void
    {
        $_SERVER['GITHUB_REPOSITORY'] = 'foo';
        $_SERVER['GITHUB_PULL_REQUEST_ID'] = 'foo';
        $_SERVER['GITHUB_TOKEN'] = 'foo';

        $github = $this->createMock(Github::class);
        $github->expects(static::once())->method('load');

        $detector = new PlatformDetector($github, $this->createMock(Gitlab::class));
        static::assertSame($github, $detector->detect());

        unset($_SERVER['GITHUB_REPOSITORY']);
        unset($_SERVER['GITHUB_PULL_REQUEST_ID']);
        unset($_SERVER['GITHUB_TOKEN']);
    }

    public function testGitlab(): void
    {
        $_SERVER['GITLAB_CI'] = 'foo';
        $_SERVER['CI_PROJECT_ID'] = 'foo';
        $_SERVER['CI_MERGE_REQUEST_IID'] = 'foo';
        $_SERVER['DANGER_GITLAB_TOKEN'] = 'foo';

        $gitlab = $this->createMock(Gitlab::class);
        $gitlab->expects(static::once())->method('load');

        $detector = new PlatformDetector($this->createMock(Github::class), $gitlab);
        static::assertSame($gitlab, $detector->detect());

        unset($_SERVER['GITLAB_CI']);
        unset($_SERVER['CI_PROJECT_ID']);
        unset($_SERVER['CI_MERGE_REQUEST_IID']);
        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }
}
