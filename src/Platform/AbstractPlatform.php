<?php
declare(strict_types=1);

namespace Danger\Platform;

use Danger\Config;
use Danger\Struct\PullRequest;

abstract class AbstractPlatform
{
    public PullRequest $pullRequest;

    /**
     * @var array<string, string>
     */
    public array $raw = [];

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function load(string $projectIdentifier, string $id): void;

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function post(string $body, Config $config): string;

    /**
     * @internal Only for internal Danger usage
     */
    abstract public function removePost(Config $config): void;

    protected function addLabels(string ...$labels): void
    {
        foreach ($labels as $label) {
            $this->pullRequest->labels[] = $label;
        }

        $this->pullRequest->labels = array_unique($this->pullRequest->labels);
    }

    public function removeLabels(string ...$labels): void
    {
        $prLabels = array_flip($this->pullRequest->labels);

        foreach ($labels as $label) {
            if (isset($prLabels[$label])) {
                unset($prLabels[$label]);
            }
        }

        $this->pullRequest->labels = array_flip($prLabels);
    }

    /**
     * Can be used to determine has the pull request a danger comment
     */
    abstract public function hasDangerMessage(): bool;
}
