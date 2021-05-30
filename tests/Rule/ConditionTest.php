<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Rule\Condition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConditionTest extends TestCase
{
    public function testConditionMet(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);
        $innerRuleExecuted = false;

        $rule = new Condition(
            function (Context $context) {
                return $context->platform instanceof Github;
            },
            [
                function (Context $context) use (&$innerRuleExecuted): void {
                    $innerRuleExecuted = true;
                },
            ]
        );

        $rule($context);

        static::assertTrue($innerRuleExecuted);
    }

    public function testConditionNotMet(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);
        $innerRuleExecuted = false;

        $rule = new Condition(
            function (Context $context) {
                return $context->platform instanceof Gitlab;
            },
            [
                function (Context $context) use (&$innerRuleExecuted): void {
                    $innerRuleExecuted = true;
                },
            ]
        );

        $rule($context);

        static::assertFalse($innerRuleExecuted);
    }
}
