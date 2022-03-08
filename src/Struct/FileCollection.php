<?php
declare(strict_types=1);

namespace Danger\Struct;

use function count;

/**
 * @extends Collection<File>
 */
class FileCollection extends Collection
{
    /**
     * @return FileCollection<File>
     */
    public function matches(string $pattern): self
    {
        return $this->filter(static function (File $file) use ($pattern): bool {
            return fnmatch($pattern, $file->name);
        });
    }

    /**
     * @return FileCollection<File>
     */
    public function matchesContent(string $pattern): self
    {
        return $this->filter(static function (File $file) use ($pattern): bool {
            return !(($matches = preg_grep($pattern, [$file->getContent()])) === false || count($matches) === 0);
        });
    }

    /**
     * @return FileCollection<File>
     */
    public function filterStatus(string $status): self
    {
        return $this->filter(static function (File $file) use ($status): bool {
            return $file->status === $status;
        });
    }
}
