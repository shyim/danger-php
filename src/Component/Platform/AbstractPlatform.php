<?php
declare(strict_types=1);

namespace Danger\Component\Platform;

use Danger\Component\Struct\PullRequest;
use Danger\Config;

abstract class AbstractPlatform
{
    public PullRequest $pullRequest;

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function load(string $owner, string $repository, string $id): void;

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function post(string $body, Config $config): string;

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function removePost(Config $config): void;

    abstract public function addLabels(string ...$labels): void;

    abstract public function removeLabels(string ...$labels): void;
}
