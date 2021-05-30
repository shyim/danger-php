<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
class ApplicationTest extends TestCase
{
    public function testCliAppStart(): void
    {
        $app = new Application();
        self::assertInstanceOf(ContainerInterface::class, $app->getContainer());
        static::assertInstanceOf(Command::class, $app->find('ci'));
    }
}
