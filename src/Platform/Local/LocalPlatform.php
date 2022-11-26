<?php declare(strict_types=1);

namespace Danger\Platform\Local;

use Danger\Config;
use Danger\Platform\AbstractPlatform;
use Danger\Struct\Local\LocalPullRequest;

class LocalPlatform extends AbstractPlatform
{
    private bool $didCommented = false;

    public function load(string $projectIdentifier, string $id): void
    {
        [$localBranch, $targetBranch] = explode('|', $id);

        $this->pullRequest = new LocalPullRequest($projectIdentifier, $localBranch, $targetBranch);
    }

    public function post(string $body, Config $config): string
    {
        $this->didCommented = true;

        return '';
    }

    public function removePost(Config $config): void
    {
    }

    public function hasDangerMessage(): bool
    {
        return $this->didCommented;
    }
}
