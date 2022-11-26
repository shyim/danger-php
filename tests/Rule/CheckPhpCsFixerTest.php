<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\CheckPhpCsFixer;
use Danger\Struct\FileCollection;
use Danger\Struct\Github\PullRequest;
use Danger\Tests\Struct\TestFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CheckPhpCsFixerTest extends TestCase
{
    public function testRuleRunsWithoutIssues(): void
    {
        $github = $this->createMock(Github::class);
        $pullRequest = $this->createMock(PullRequest::class);
        $github->pullRequest = $pullRequest;
        $pullRequest->method('getFiles')->willReturn(new FileCollection([
            new TestFile('test.php', '<?php
declare(strict_types=1);

var_dump(\'Test\');
'),
        ]));

        $context = new Context($github);

        $rule = new CheckPhpCsFixer();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }

    public function testRuleFailures(): void
    {
        $github = $this->createMock(Github::class);
        $pullRequest = $this->createMock(PullRequest::class);
        $github->pullRequest = $pullRequest;
        $pullRequest->method('getFiles')->willReturn(new FileCollection([
            new TestFile('test.php', '<?php   var_dump("asd");;;'),
            new TestFile('foo/test.php', '<?php   var_dump("asd");;;'),
        ]));

        $context = new Context($github);

        $rule = new CheckPhpCsFixer();
        $rule($context);

        static::assertTrue($context->hasFailures());
    }
}
