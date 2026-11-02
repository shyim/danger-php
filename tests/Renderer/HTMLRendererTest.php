<?php
declare(strict_types=1);

namespace Danger\Tests\Renderer;

use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Renderer\HTMLRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class HTMLRendererTest extends TestCase
{
    public function testRenderEmpty(): void
    {
        $renderer = new HTMLRenderer();
        static::assertSame(HTMLRenderer::MARKER, $renderer->convert(new Context(static::createStub(Github::class))));
    }

    public function testRenderFailure(): void
    {
        $renderer = new HTMLRenderer();
        $context = new Context(static::createStub(Github::class));
        $context->failure('Test');

        static::assertStringContainsString(HTMLRenderer::MARKER, $renderer->convert($context));
    }
}
