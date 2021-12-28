<?php
declare(strict_types=1);

namespace Danger\DependencyInjection\Factory;

use Github\AuthMethod;
use Github\Client;

class GithubClientFactory
{
    public static function build(): Client
    {
        $client = new Client();

        if (isset($_SERVER['GITHUB_TOKEN'])) {
            $client->authenticate($_SERVER['GITHUB_TOKEN'], null, AuthMethod::ACCESS_TOKEN);
        }

        return $client;
    }
}
