<?php
declare(strict_types=1);

namespace Danger\Tests\Rule;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Rule\CheckPhpStan;
use function dirname;
use PHPUnit\Framework\TestCase;
use function unlink;

/**
 * @internal
 */
class CheckPhpStanTest extends TestCase
{
    public function testValid(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);

        $rule = new CheckPhpStan();
        $rule($context);

        static::assertFalse($context->hasFailures());
    }

    public function testInvalid(): void
    {
        $github = $this->createMock(Github::class);
        $context = new Context($github);

        $path = dirname(__DIR__, 2) . '/src/Test.php';

        file_put_contents($path, '<?php str_contains(new ArrayObject());');

        $rule = new CheckPhpStan();
        $rule($context);

        static::assertTrue($context->hasFailures());

        unlink($path);
    }
}
