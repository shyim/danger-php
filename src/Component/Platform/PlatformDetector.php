<?php
declare(strict_types=1);

namespace Danger\Component\Platform;

use Danger\Component\Platform\Github\Github;
use Danger\Exception\UnsupportedCIException;

class PlatformDetector
{
    public function __construct(private Github $github)
    {
    }

    public function detect(): AbstractPlatform
    {
        if (isset($_SERVER['GITHUB_REPOSITORY']) && isset($_SERVER['GITHUB_PULL_REQUEST_ID']) && isset($_SERVER['GITHUB_TOKEN'])) {
            return $this->createFromGithubContext();
        }

        throw new UnsupportedCIException();
    }

    private function createFromGithubContext(): AbstractPlatform
    {
        [$owner, $repo] = explode('/', $_SERVER['GITHUB_REPOSITORY']);

        $this->github->load($owner, $repo, $_SERVER['GITHUB_PULL_REQUEST_ID']);

        return $this->github;
    }
}
