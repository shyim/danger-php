<?php
declare(strict_types=1);

namespace Danger\DependencyInjection\Factory;

use Gitlab\Client;

class GitlabClientFactory
{
    public static function build(): Client
    {
        $client = new Client();

        if (isset($_SERVER['CI_SERVER_URL'])) {
            $client->setUrl($_SERVER['CI_SERVER_URL']);
        }

        if (isset($_SERVER['DANGER_GITLAB_TOKEN'])) {
            $client->authenticate($_SERVER['DANGER_GITLAB_TOKEN'], Client::AUTH_HTTP_TOKEN);
        }

        return $client;
    }
}
