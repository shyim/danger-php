<?php
declare(strict_types=1);

namespace Danger\Struct\Github;

class File extends \Danger\Struct\File
{
    private ?string $content = null;

    public function __construct(private string $rawUrl)
    {
    }

    public function getContent(): string
    {
        return $this->content ?? ($this->content = (string) file_get_contents($this->rawUrl));
    }
}
