<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\CheckPhpStan instead
 */
class CheckPhpStanRule extends CheckPhpStan
{
    public function __construct(
        string $command = './vendor/bin/phpstan --error-format=json --no-progress',
        string $message = 'PHPStan check failed. Run locally <code>./vendor/bin/phpstan --error-format=json --no-progress</code> to see the errors.'
    ) {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, CheckPhpStan::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($command, $message);
    }
}
