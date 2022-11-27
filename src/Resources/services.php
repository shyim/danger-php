<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

use Danger\DependencyInjection\Factory\GithubClientFactory;
use Danger\DependencyInjection\Factory\GitlabClientFactory;
use Github\Client as GithubClient;
use Gitlab\Client as GitlabClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $configurator): void {
    $configurator
        ->services()
        ->defaults()
        ->public()
        ->autowire()
        ->autoconfigure()
        ->load('Danger\\', dirname(__DIR__))
        ->exclude(dirname(__DIR__) . '/{Struct,Exception,Rule,Resources,Context.php}')
    ;

    $configurator
        ->services()
        ->set(GithubClient::class)
        ->factory([GithubClientFactory::class, 'build'])
    ;

    $configurator
        ->services()
        ->set(GitlabClient::class)
        ->factory([GitlabClientFactory::class, 'build'])
    ;

    $configurator
        ->services()
        ->set(HttpClientInterface::class)
        ->factory([HttpClient::class, 'create'])
    ;
};
