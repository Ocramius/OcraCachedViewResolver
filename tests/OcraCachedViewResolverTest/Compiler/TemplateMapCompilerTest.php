<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace OcraCachedViewResolverTest\Compiler;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use OcraCachedViewResolver\Compiler\TemplateMapCompiler;
use Laminas\Stdlib\SplStack;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;

/**
 * Template map compiler tests
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler
 */
class TemplateMapCompilerTest extends TestCase
{
    /**
     * @var TemplateMapCompiler
     */
    protected $compiler;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new TemplateMapCompiler();
    }

    public function testCompileFromUnknownResolverProducesEmptyMap()
    {
        /* @var $resolver ResolverInterface */
        $resolver = $this->createMock(ResolverInterface::class);

        self::assertSame([], $this->compiler->compileMap($resolver));
    }

    public function testCompileFromMapResolver()
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

    public function testCompileFromTemplatePathStack()
    {
        $templatePathStack = $this->createMock(TemplatePathStack::class);
        $paths = $this->createMock(SplStack::class);
        $paths
            ->method('toArray')
            ->will(self::returnValue([__DIR__ . '/_files/subdir2', __DIR__ . '/_files/subdir1']));

        $templatePathStack
            ->method('getPaths')
            ->will(self::returnValue($paths));
        $templatePathStack
        ->method('resolve')
        ->willReturnCallback(function ($name) {
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

    public function testCompileFromAggregateResolver()
    {
        $aggregateResolver = $this->createMock(AggregateResolver::class);
        $mapResolver1 = $this->createMock(TemplateMapResolver::class);
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
