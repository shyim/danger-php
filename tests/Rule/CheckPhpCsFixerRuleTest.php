<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Component\Platform\Github\Github;
use Danger\Context;
use Danger\Rule\CheckPhpCsFixerRule;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CheckPhpCsFixerRuleTest extends TestCase
{
    public function testRuleRuns(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);

        $rule = new CheckPhpCsFixerRule();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }
}
