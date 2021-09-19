<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\Compiler;

use ArrayIterator;
use Laminas\Stdlib\SplStack;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use OcraCachedViewResolver\Compiler\TemplateMapCompiler;
use PHPUnit\Framework\TestCase;

use function assert;
use function realpath;

/**
 * Template map compiler tests
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler
 */
class TemplateMapCompilerTest extends TestCase
{
    protected TemplateMapCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new TemplateMapCompiler();
    }

    public function testCompileFromUnknownResolverProducesEmptyMap(): void
    {
        $resolver = $this->createMock(ResolverInterface::class);
        assert($resolver instanceof ResolverInterface);

        self::assertSame([], $this->compiler->compileMap($resolver));
    }

    public function testCompileFromMapResolver(): void
    {
        $mapResolver = $this->createMock(TemplateMapResolver::class);
        $mapResolver
            ->method('getMap')
            ->will(self::returnValue(['a' => 'b', 'c' => 'd']));

        $map = $this->compiler->compileMap($mapResolver);

        self::assertCount(2, $map);
        self::assertSame('b', $map['a']);
        self::assertSame('d', $map['c']);
    }

    public function testCompileFromTemplatePathStack(): void
    {
        $templatePathStack = $this->createMock(TemplatePathStack::class);
        $paths             = $this->createMock(SplStack::class);
        $paths
            ->method('toArray')
            ->will(self::returnValue([__DIR__ . '/_files/subdir2', __DIR__ . '/_files/subdir1']));

        $templatePathStack
            ->method('getPaths')
            ->will(self::returnValue($paths));
        $templatePathStack
        ->method('resolve')
        ->willReturnCallback(static function ($name) {
            $keys = [
                'template2'       => __DIR__ . '/_files/subdir2/template2.phtml',
                'template3'       => false,
                'valid/template4' => __DIR__ . '/_files/subdir1/valid/template4.phtml',
            ];

            return $keys[$name];
        });
        $map = $this->compiler->compileMap($templatePathStack);

        self::assertCount(2, $map);

        $template2 = realpath(__DIR__ . '/_files/subdir2/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        self::assertIsString($template2);
        self::assertIsString($template4);

        self::assertSame($template2, $map['template2']);
        self::assertSame($template4, $map['valid/template4']);
    }

    public function testCompileFromAggregateResolver(): void
    {
        $aggregateResolver = $this->createMock(AggregateResolver::class);
        $mapResolver1      = $this->createMock(TemplateMapResolver::class);
        $mapResolver1
            ->method('getMap')
            ->will(self::returnValue(['a' => 'a-value', 'b' => 'b-value']));
        $mapResolver2 = $this->createMock(TemplateMapResolver::class);
        $mapResolver2
            ->method('getMap')
            ->will(self::returnValue(['c' => 'c-value', 'd' => 'd-value']));
        $mapResolver3 = $this->createMock(TemplateMapResolver::class);
        $mapResolver3
            ->method('getMap')
            ->will(self::returnValue(['a' => 'override-a-value', 'd' => 'override-d-value', 'e' => 'e-value']));

        $iterator = new ArrayIterator([$mapResolver1, $mapResolver2, $mapResolver3]);
        $aggregateResolver
            ->method('getIterator')
            ->will(self::returnValue($iterator));

        $map = $this->compiler->compileMap($aggregateResolver);

        self::assertCount(5, $map);
        self::assertSame('a-value', $map['a']); // should not be overridden
        self::assertSame('b-value', $map['b']);
        self::assertSame('c-value', $map['c']);
        self::assertSame('d-value', $map['d']); // should not be overridden
        self::assertSame('e-value', $map['e']);
    }
}
