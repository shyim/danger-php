<?php
declare(strict_types=1);

namespace Danger\Tests\Struct;

use Danger\Struct\File;

class TestFile extends File
{
    public function __construct(public string $name, private string $content)
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
