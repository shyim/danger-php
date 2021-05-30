<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @template T
 * @extends Collection<Comment>
 *
 * @method void add(Comment $entity)
 * @method void set(string|int $key, Comment $entity)
 * @method \Generator<Comment> getIterator()
 * @method Comment[] getElements()
 * @method Comment|null get(string|int $key)
 * @method Comment|null first()
 * @method Comment|null last()
 */
class CommentCollection extends Collection
{
}
