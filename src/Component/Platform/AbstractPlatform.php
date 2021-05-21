<?php
declare(strict_types=1);

namespace Danger\Component\Platform;

use Danger\Component\Struct\PullRequest;
use Danger\Config;

abstract class AbstractPlatform
{
    public PullRequest $pullRequest;

    abstract public function load(string $owner, string $repository, string $id): void;

    abstract public function post(string $body, Config $config): string;

    abstract public function removePost(Config $config): void;
}
