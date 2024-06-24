<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;

class DisallowRepeatedCommits
{
    public function __construct(private string $message = 'You should not use the same commit message multiple times')
    {
    }

    public function __invoke(Context $context): void
    {
        $messages = $context->platform->pullRequest->getCommits()->getMessages();

        if ($context->platform instanceof Github) {
            $messages = array_filter(
                $messages,
                fn ($message) => !(preg_match('/^Merge branch .* into .*$/', $message) === 1),
            );
        }

        if (\count($messages) !== \count(array_unique($messages))) {
            $context->failure($this->message);
        }
    }
}
