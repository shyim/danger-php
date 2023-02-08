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
            static fn (Context $context): bool => $context->platform instanceof Github,
            [
                static function () use (&$innerRuleExecuted): void {
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
            static fn (Context $context): bool => $context->platform instanceof Gitlab,
            [
                static function () use (&$innerRuleExecuted): void {
                    $innerRuleExecuted = true;
                },
            ]
        );

        $rule($context);

        static::assertFalse($innerRuleExecuted);
    }
}
