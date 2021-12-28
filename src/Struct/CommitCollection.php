<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @template T
 * @extends Collection<Commit>
 *
 * @method void add(Commit $entity)
 * @method void set(string|int $key, Commit $entity)
 * @method \Generator<Commit> getIterator()
 * @method Commit[] getElements()
 * @method Commit|null get(string|int $key)
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
        return array_map(static function (Commit $commit): string {
            return $commit->message;
        }, $this->elements);
    }
}
