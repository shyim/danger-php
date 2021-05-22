<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Component\Platform\Github\Github;
use Danger\Component\Struct\Commit;
use Danger\Component\Struct\CommitCollection;
use Danger\Component\Struct\Github\PullRequest;
use Danger\Context;
use Danger\Rule\MaxCommitRule;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MaxCommitRuleTest extends TestCase
{
    public function testRuleMatches(): void
    {
        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([new Commit(), new Commit()]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new MaxCommitRule();
        $rule($context);

        static::assertTrue($context->hasFailures());
    }

    public function testRuleNotMatches(): void
    {
        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([new Commit()]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new MaxCommitRule();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }
}
