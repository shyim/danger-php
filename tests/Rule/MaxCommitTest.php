<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\MaxCommit;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\Github\PullRequest;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MaxCommitTest extends TestCase
{
    public function testRuleMatches(): void
    {
        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([new Commit(), new Commit()]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new MaxCommit();
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

        $rule = new MaxCommit();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }
}
