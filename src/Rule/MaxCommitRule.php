<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\MaxCommit instead
 */
class MaxCommitRule extends MaxCommit
{
    public function __construct(int $maxCommits = 1, string $message = 'Please squash your commits to ###AMOUNT### only')
    {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, MaxCommit::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($maxCommits, $message);
    }
}
