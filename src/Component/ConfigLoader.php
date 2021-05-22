<?php
declare(strict_types=1);

namespace Danger\Component;

use Danger\Config;
use Danger\Exception\InvalidConfigurationException;

class ConfigLoader
{
    public function loadByPath(?string $path): Config
    {
        if ($path && file_exists($path)) {
            $c = require $path;

            assert($c instanceof Config);

            return $c;
        }

        $path = getcwd() . '/.danger.php';

        if (file_exists($path)) {
            $c = require $path;

            assert($c instanceof Config);

            return $c;
        }

        throw new InvalidConfigurationException('Cannot find .danger.php in your Project');
    }
}
