<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\CheckPhpCsFixer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CheckPhpCsFixerTest extends TestCase
{
    public function testRuleRunsWithoutIssues(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);

        $rule = new CheckPhpCsFixer();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }

    public function testRuleFailures(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);

        $path = dirname(__DIR__, 2) . '/src/Test.php';

        file_put_contents($path, '<?php var_dump(\'foo\');;;');

        $rule = new CheckPhpCsFixer();
        $rule($context);

        static::assertTrue($context->hasFailures());

        \unlink($path);
    }
}
