<?php
declare(strict_types=1);

namespace Danger\Exception;

class UnsupportedCIException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Could not detect CI Platform');
    }
}
