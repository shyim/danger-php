<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

class CommitRegex
{
    public function __construct(private string $regex, private string $message = 'The commit message "###MESSAGE###" does not match the regex ###REGEX###')
    {
    }

    public function __invoke(Context $context): void
    {
        foreach ($context->platform->pullRequest->getCommits() as $commit) {
            $pregMatch = preg_match($this->regex, $commit->message);

            if ($pregMatch === 0 || $pregMatch === false) {
                $context->failure(str_replace(['###MESSAGE###', '###REGEX###'], [$commit->message, $this->regex], $this->message));
            }
        }
    }
}
