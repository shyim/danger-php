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
        /** @var string $repository */
        $repository = $_SERVER['GITHUB_REPOSITORY'];
        /** @var string $pullRequestId */
        $pullRequestId = $_SERVER['GITHUB_PULL_REQUEST_ID'];

        $this->github->load($repository, $pullRequestId);

        return $this->github;
    }

    private function createFromGitlabContext(): AbstractPlatform
    {
        /** @var string $projectId */
        $projectId = $_SERVER['CI_PROJECT_ID'];
        /** @var string $mergeRequestIid */
        $mergeRequestIid = $_SERVER['CI_MERGE_REQUEST_IID'];

        $this->gitlab->load($projectId, $mergeRequestIid);

        return $this->gitlab;
    }
}
