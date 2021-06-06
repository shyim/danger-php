<?php
declare(strict_types=1);

namespace Danger;

use Danger\Exception\InvalidConfigurationException;

class ConfigLoader
{
    public function loadByPath(?string $path): Config
    {
        if (null === $path) {
            $path = '.danger.php';
        }

        if (!file_exists($path)) {
            throw new InvalidConfigurationException(sprintf('Cannot find %s in your Project', $path));
        }

        $c = require $path;

        assert($c instanceof Config);

        return $c;
    }
}
