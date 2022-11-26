<?php declare(strict_types=1);

namespace Danger\Struct\Local;

use Danger\Struct\File;

class LocalFile extends File
{
    public function __construct(private string $file)
    {
    }

    public function getContent(): string
    {
        return (string) file_get_contents($this->file);
    }
}
