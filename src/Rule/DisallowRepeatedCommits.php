<?php
declare(strict_types=1);

namespace Danger\Rule;

use function count;

use Danger\Context;

class DisallowRepeatedCommits
{
    public function __construct(private string $message = 'You should not use the same commit message multiple times')
    {
    }

    public function __invoke(Context $context): void
    {
        $messages = $context->platform->pullRequest->getCommits()->getMessages();

        if (count($messages) !== count(array_unique($messages))) {
            $context->failure($this->message);
        }
    }
}
