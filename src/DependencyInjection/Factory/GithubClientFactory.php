<?php
declare(strict_types=1);

namespace Danger\DependencyInjection\Factory;

use Github\Client;

class GithubClientFactory
{
    public static function build(): Client
    {
        $client = new Client();

        if (isset($_SERVER['GITHUB_TOKEN'])) {
            $client->authenticate($_SERVER['GITHUB_TOKEN'], null, Client::AUTH_ACCESS_TOKEN);
        }

        return $client;
    }
}
