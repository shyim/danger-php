<?php
declare(strict_types=1);

namespace Danger\Tests\DependencyInjection\Factory;

use Danger\DependencyInjection\Factory\GitlabClientFactory;
use Gitlab\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GitlabClientFactoryTest extends TestCase
{
    public function testBuild(): void
    {
        $_SERVER['CI_SERVER_URL'] = 'http://localhost';
        $_SERVER['DANGER_GITLAB_TOKEN'] = '1';

        static::assertInstanceOf(Client::class, GitlabClientFactory::build());

        unset($_SERVER['CI_SERVER_URL']);
        unset($_SERVER['DANGER_GITLAB_TOKEN']);
    }
}
