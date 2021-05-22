<?php
declare(strict_types=1);

namespace Danger;

use Danger\Exception\InvalidConfigurationException;

class ConfigLoader
{
    public function loadByPath(?string $path): Config
    {
        if ($path) {
            if (!file_exists($path)) {
                throw new InvalidConfigurationException(sprintf('Cannot find %s in your Project', $path));
            }

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
