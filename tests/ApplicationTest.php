<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Application;
use Danger\Command\CiCommand;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ApplicationTest extends TestCase
{
    /**
     * Tests also that the container can be built
     */
    public function testCliAppStart(): void
    {
        $_SERVER['CI_SERVER_URL'] = 'https://gitlab.com';
        $_SERVER['DANGER_GITLAB_TOKEN'] = '1';
        $_SERVER['GITHUB_TOKEN'] = '1';

        $app = new Application();

        static::assertTrue($app->getContainer()->has(CiCommand::class));

        unset($_SERVER['CI_SERVER_URL']);
        unset($_SERVER['DANGER_GITLAB_TOKEN']);
        unset($_SERVER['GITHUB_TOKEN']);
    }
}
