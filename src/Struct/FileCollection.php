<?php
declare(strict_types=1);

namespace Danger\Struct;

/**
 * @template T
 * @extends Collection<File>
 *
 * @method void add(File $entity)
 * @method void set(string $key, File $entity)
 * @method File[] getIterator()
 * @method File[] getElements()
 * @method File|null get(string $key)
 * @method File|null first()
 * @method File|null last()
 */
class FileCollection extends Collection
{
    /**
     * @return FileCollection<File>
     */
    public function matches(string $pattern): self
    {
        return $this->filter(function (File $file) use ($pattern) {
            return fnmatch($pattern, $file->name);
        });
    }

    /**
     * @return FileCollection<File>
     */
    public function filterStatus(string $status): self
    {
        return $this->filter(function (File $file) use ($status) {
            return $file->status === $status;
        });
    }
}
