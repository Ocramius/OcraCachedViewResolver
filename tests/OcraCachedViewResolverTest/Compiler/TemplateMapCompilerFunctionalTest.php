<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\Compiler;

use Laminas\View\Resolver\TemplatePathStack;
use OcraCachedViewResolver\Compiler\TemplateMapCompiler;
use PHPUnit\Framework\TestCase;

use function realpath;

/**
 * Template map compiler functional tests
 *
 * @group Functional
 * @coversNothing
 */
class TemplateMapCompilerFunctionalTest extends TestCase
{
    protected TemplateMapCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new TemplateMapCompiler();
    }

    public function testCompileFromTemplatePathStack(): void
    {
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/_files/subdir1');
        $resolver->addPath(__DIR__ . '/_files/subdir2');

        $template2 = realpath(__DIR__ . '/_files/subdir2/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        self::assertIsString($template2);
        self::assertIsString($template4);

        self::assertSame(
            [
                'template2'       => $template2,
                'valid/template4' => $template4,
            ],
            $this->compiler->compileMap($resolver),
        );
    }

    public function testCompileFromTemplatePathStackWithDifferentPaths(): void
    {
        $template2 = realpath(__DIR__ . '/_files/subdir1/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        self::assertIsString($template2);
        self::assertIsString($template4);

        // inverse directory order
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/_files/subdir2');
        $resolver->addPath(__DIR__ . '/_files/subdir1');

        $map = $this->compiler->compileMap($resolver);

        self::assertCount(2, $map);
        self::assertSame($template2, $map['template2']);
        self::assertSame($template4, $map['valid/template4']);
    }
}
