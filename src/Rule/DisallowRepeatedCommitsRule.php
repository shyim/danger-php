<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @noinspection PhpUnused
 *
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\DisallowRepeatedCommits instead
 */
class DisallowRepeatedCommitsRule extends DisallowRepeatedCommits
{
    public function __construct(string $message = 'You should not use the same commit message multiple times')
    {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, DisallowRepeatedCommits::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($message);
    }
}
