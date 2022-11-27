<?php declare(strict_types=1);

namespace Danger\Exception;

class CouldNotGetFileContentException extends \RuntimeException
{
    public function __construct(string $path, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Could not get content of file %s', $path), 0, $previous);
    }
}
