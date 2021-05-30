<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\DisallowRepeatedCommits;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\Github\PullRequest;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DisallowRepeatedCommitsTest extends TestCase
{
    public function testRuleMatches(): void
    {
        $commit = new Commit();
        $commit->message = 'Test';

        $secondCommit = new Commit();
        $secondCommit->message = 'Test';

        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([$commit, $secondCommit]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new DisallowRepeatedCommits();
        $rule($context);

        static::assertTrue($context->hasFailures());
    }

    public function testRuleNotMatches(): void
    {
        $commit = new Commit();
        $commit->message = 'Test';

        $secondCommit = new Commit();
        $secondCommit->message = 'Test2';

        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([$commit, $secondCommit]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new DisallowRepeatedCommits();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }
}
