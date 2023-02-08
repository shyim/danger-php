<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @extends Collection<Commit>
 */
class CommitCollection extends Collection
{
    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return array_map(static fn (Commit $commit): string => $commit->message, $this->elements);
    }
}
