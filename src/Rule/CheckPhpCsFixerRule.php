<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\CheckPhpCsFixer instead
 */
class CheckPhpCsFixerRule extends CheckPhpCsFixer
{
    public function __construct(
        string $command = 'php vendor/bin/php-cs-fixer fix --format=json',
        string $executionFailed = 'PHP-CS-Fixer did not run',
        string $foundErrors = 'Found some Code-Style issues. Please run <code>./vendor/bin/php-cs-fixer fix</code> on your branch'
    ) {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, CheckPhpCsFixer::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($command, $executionFailed, $foundErrors);
    }
}
