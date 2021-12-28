<?php
declare(strict_types=1);

namespace Danger\DependencyInjection;

use function dirname;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class Container
{
    public static function getContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerForAutoconfiguration(Command::class)->addTag('console.command');

        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources'));
        $loader->load('services.php');
        $container->compile();

        return $container;
    }
}
