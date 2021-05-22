<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

/**
 * Runs PhpCsFixer and adds a failure to danger if failing
 */
class CheckPhpCsFixerRule
{
    public function __invoke(Context $context): void
    {
        exec('php vendor/bin/php-cs-fixer fix --format=json', $cmdOutput, $resultCode);

        // @codeCoverageIgnoreStart
        if (!isset($cmdOutput[0])) {
            $context->failure('PHP-CS-Fixer did not run');
        }
        // @codeCoverageIgnoreEnd

        if (count(json_decode($cmdOutput[0], true)['files'])) {
            $context->failure('Found some Code-Style issues. Please run <code>./vendor/bin/php-cs-fixer fix</code> on your branch');
        }
    }
}
