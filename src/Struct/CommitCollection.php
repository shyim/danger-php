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
        return array_map(static function (Commit $commit): string {
            return $commit->message;
        }, $this->elements);
    }
}
