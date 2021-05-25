<?php
declare(strict_types=1);

namespace Danger\Platform;

use Danger\Exception\UnsupportedCIException;
use Danger\Platform\Github\Github;
use Danger\Platform\Gitlab\Gitlab;

class PlatformDetector
{
    public function __construct(private Github $github, private Gitlab $gitlab)
    {
    }

    public function detect(): AbstractPlatform
    {
        if (isset($_SERVER['GITHUB_REPOSITORY'], $_SERVER['GITHUB_PULL_REQUEST_ID'], $_SERVER['GITHUB_TOKEN'])) {
            return $this->createFromGithubContext();
        }

        if (isset($_SERVER['GITLAB_CI'], $_SERVER['CI_PROJECT_ID'], $_SERVER['CI_MERGE_REQUEST_IID'], $_SERVER['DANGER_GITLAB_TOKEN'])) {
            return $this->createFromGitlabContext();
        }

        throw new UnsupportedCIException();
    }

    private function createFromGithubContext(): AbstractPlatform
    {
        $this->github->load($_SERVER['GITHUB_REPOSITORY'], $_SERVER['GITHUB_PULL_REQUEST_ID']);

        return $this->github;
    }

    private function createFromGitlabContext(): AbstractPlatform
    {
        $this->gitlab->load($_SERVER['CI_PROJECT_ID'], $_SERVER['CI_MERGE_REQUEST_IID']);

        return $this->gitlab;
    }
}
