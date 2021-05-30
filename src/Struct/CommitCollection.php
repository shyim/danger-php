<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @template T
 * @extends Collection<Commit>
 *
 * @method void add(Commit $entity)
 * @method void set(string $key, Commit $entity)
 * @method Commit[] getIterator()
 * @method Commit[] getElements()
 * @method Commit|null get(string $key)
 * @method Commit|null first()
 * @method Commit|null last()
 */
class CommitCollection extends Collection
{
    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->fmap(function (Commit $commit) {
            return $commit->message;
        });
    }
}
