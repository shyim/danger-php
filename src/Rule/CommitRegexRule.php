<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\CommitRegex instead
 */
class CommitRegexRule extends CommitRegex
{
    public function __construct(string $regex, string $message = 'The commit message "###MESSAGE###" does not match the regex ###REGEX###')
    {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, CommitRegex::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($regex, $message);
    }
}
