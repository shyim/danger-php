<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\Config;
use Danger\ConfigLoader;
use Danger\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigLoaderTest extends TestCase
{
    public function testLoadingByDefaultPath(): void
    {
        $loader = new ConfigLoader();

        static::assertInstanceOf(Config::class, $loader->loadByPath(null));
    }

    public function testLoadingExplicitPath(): void
    {
        $loader = new ConfigLoader();

        static::assertInstanceOf(Config::class, $loader->loadByPath(dirname(__DIR__) . '/.danger.php'));
    }

    public function testLoadingNotExistingFile(): void
    {
        $loader = new ConfigLoader();

        static::expectException(InvalidConfigurationException::class);

        static::assertInstanceOf(Config::class, $loader->loadByPath(dirname(__DIR__) . '/danger.php'));
    }

    public function testLoadingWithoutFile(): void
    {
        $currentDir = getcwd();
        static::assertIsString($currentDir);
        chdir('/tmp');

        $loader = new ConfigLoader();

        static::expectException(InvalidConfigurationException::class);

        static::assertInstanceOf(Config::class, $loader->loadByPath(null));
        chdir($currentDir);
    }
}
