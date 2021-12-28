<?php
declare(strict_types=1);

namespace Danger\Rule;

use Danger\Application;

/**
 * @noinspection PhpUnused
 * @codeCoverageIgnore
 *
 * @deprecated use \Danger\Rule\Condition instead
 */
class ConditionRule extends Condition
{
    /**
     * {@inheritDoc}
     */
    public function __construct(callable $condition, array $rules)
    {
        $deprecationMessage = sprintf(Application::RULE_DEPRECATION_MESSAGE, Condition::class);
        trigger_deprecation(Application::PACKAGE_NAME, '0.1.5', $deprecationMessage);

        parent::__construct($condition, $rules);
    }
}
