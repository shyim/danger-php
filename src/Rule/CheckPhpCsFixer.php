<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Context;

/**
 * Runs PhpCsFixer and adds a failure to danger if failing
 */
class CheckPhpCsFixer
{
    public function __construct(
        private string $command = 'php vendor/bin/php-cs-fixer fix --format=json',
        private string $executionFailed = 'PHP-CS-Fixer did not run',
        private string $foundErrors = 'Found some Code-Style issues. Please run <code>./vendor/bin/php-cs-fixer fix</code> on your branch'
    ) {
    }

    public function __invoke(Context $context): void
    {
        exec($this->command, $cmdOutput, $resultCode);

        // @codeCoverageIgnoreStart
        if (!isset($cmdOutput[0])) {
            $context->failure($this->executionFailed);
        }
        // @codeCoverageIgnoreEnd

        if (count(json_decode($cmdOutput[0], true)['files'])) {
            $context->failure($this->foundErrors);
        }
    }
}
