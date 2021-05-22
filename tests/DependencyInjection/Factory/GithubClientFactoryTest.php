<?php
declare(strict_types=1);

namespace Danger\Tests\DependencyInjection\Factory;

use Danger\DependencyInjection\Factory\GithubClientFactory;
use Github\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GithubClientFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        static::assertInstanceOf(Client::class, GithubClientFactory::build());
    }

    public function testFactoryWithToken(): void
    {
        $_SERVER['GITHUB_TOKEN'] = 'abc';

        static::assertInstanceOf(Client::class, GithubClientFactory::build());

        unset($_SERVER['GITHUB_TOKEN']);
    }
}
