<?php
declare(strict_types=1);

namespace Danger\Component\Struct\Github;

class File extends \Danger\Component\Struct\File
{
    public function __construct(private string $rawUrl)
    {
    }

    public function getContent(): string
    {
        return file_get_contents($this->rawUrl);
    }
}
