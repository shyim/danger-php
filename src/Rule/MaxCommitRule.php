<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

class MaxCommitRule
{
    public function __construct(private int $maxCommits = 1, private string $message = 'Please squash your commits to ###AMOUNT### commit(s) only')
    {
    }

    public function __invoke(Context $context): void
    {
        if (count($context->platform->pullRequest->getCommits()) > $this->maxCommits) {
            $context->failure(str_replace(['###AMOUNT###'], [$this->maxCommits], $this->message));
        }
    }
}
