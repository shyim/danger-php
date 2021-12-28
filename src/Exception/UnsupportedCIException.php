<?php
declare(strict_types=1);

namespace Danger\Exception;

use RuntimeException;

class UnsupportedCIException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Could not detect CI Platform');
    }
}
