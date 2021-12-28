<?php
declare(strict_types=1);

namespace Danger;

use function assert;
use Danger\Exception\InvalidConfigurationException;

class ConfigLoader
{
    /** @noinspection UsingInclusionReturnValueInspection */
    public function loadByPath(?string $path): Config
    {
        if ($path === null) {
            $path = getcwd() . '/.danger.php';
        }

        if (!file_exists($path)) {
            throw new InvalidConfigurationException(sprintf('Cannot find %s in your Project', $path));
        }

        $c = require $path;

        assert($c instanceof Config);

        return $c;
    }
}
