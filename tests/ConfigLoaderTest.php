<?php
declare(strict_types=1);

namespace Danger\Tests;

use Danger\ConfigLoader;
use Danger\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigLoaderTest extends TestCase
{
    public function testLoadingNotExistingFile(): void
    {
        $loader = new ConfigLoader();

        $this->expectException(InvalidConfigurationException::class);

        $loader->loadByPath(\dirname(__DIR__) . '/danger.php');
    }

    public function testLoadingWithoutFile(): void
    {
        $currentDir = getcwd();
        static::assertIsString($currentDir);
        chdir('/tmp');

        $loader = new ConfigLoader();

        $this->expectException(InvalidConfigurationException::class);

        $loader->loadByPath(null);

        chdir($currentDir);
    }
}
