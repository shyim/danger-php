<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\CommitRegexRule;
use Danger\Struct\Commit;
use Danger\Struct\CommitCollection;
use Danger\Struct\Github\PullRequest;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CommitRegexRuleTest extends TestCase
{
    public function testRuleMatches(): void
    {
        $commit = new Commit();
        $commit->message = 'Test';

        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([$commit]));
        $github->pullRequest = $pr;

        $context = new Context($github);

        $rule = new CommitRegexRule('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m');
        $rule($context);

        static::assertTrue($context->hasFailures());
    }

    public function testRuleNotMatches(): void
    {
        $commit = new Commit();
        $commit->message = 'feat: Test';

        $github = $this->createMock(Github::class);
        $pr = $this->createMock(PullRequest::class);
        $pr->method('getCommits')->willReturn(new CommitCollection([$commit]));
        $github->pullRequest = $pr;

        $context = new Context($github);
        $rule = new CommitRegexRule('/^(feat|fix|docs|perf|refactor|compat|chore)(\(.+\))?\:\s(.{3,})/m');

        $rule($context);

        static::assertFalse($context->hasFailures());
    }
}
