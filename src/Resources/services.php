<?php
declare(strict_types=1);

use Danger\DependencyInjection\Factory\GithubClientFactory;
use Github\Client as GithubClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator
        ->services()
        ->defaults()
        ->public()
        ->autowire()
        ->autoconfigure()
        ->load('Danger\\Command\\', dirname(__DIR__) . '/Command')
        ->load('Danger\\Component\\', dirname(__DIR__) . '/Component')
        ->exclude(dirname(__DIR__) . '/Component/Struct')
    ;

    $configurator
        ->services()
        ->set(GithubClient::class)
        ->factory([GithubClientFactory::class, 'build'])
    ;
};
