<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

class MaxCommitRule
{
    public function __construct(private int $maxCommits = 1, private string $message = 'Please squash your commits to ###AMOUNT### only')
    {
    }

    public function __invoke(Context $context): void
    {
        if (count($context->platform->pullRequest->getCommits()) > $this->maxCommits) {
            $message = $this->maxCommits . ' commits';
            if ($this->maxCommits === 1) {
                $message = 'one commit';
            }

            $context->failure(str_replace(['###AMOUNT###'], [$message], $this->message));
        }
    }
}
