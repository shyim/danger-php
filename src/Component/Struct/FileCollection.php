<?php
declare(strict_types=1);

namespace Danger\Component\Struct;

/**
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
    protected function getExpectedClass(): ?string
    {
        return File::class;
    }

    public function hasAddedFile(string $fileName): bool
    {
        return $this->filter(function (File $file) use ($fileName) {
            return $file->name === $fileName && $file->status === File::STATUS_ADDED;
        })->first() !== null;
    }

    public function hasModifiedFile(string $fileName): bool
    {
        return $this->filter(function (File $file) use ($fileName) {
            return $file->name === $fileName && $file->status === File::STATUS_MODIFIED;
        })->first() !== null;
    }

    public function hasRemovedFile(string $fileName): bool
    {
        return $this->filter(function (File $file) use ($fileName) {
            return $file->name === $fileName && $file->status === File::STATUS_REMOVED;
        })->first() !== null;
    }
}
