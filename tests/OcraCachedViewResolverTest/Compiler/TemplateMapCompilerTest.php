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

use PHPUnit_Framework_TestCase;
use OcraCachedViewResolver\Compiler\TemplateMapCompiler;

/**
 * Template map compiler tests
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class TemplateMapCompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateMapCompiler
     */
    protected $compiler;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->compiler = new TemplateMapCompiler();
    }

    /**
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileMap
     */
    public function testCompileFromUnknownResolverProducesEmptyMap()
    {
        $this->assertSame(array(), $this->compiler->compileMap($this->getMock('Zend\View\Resolver\ResolverInterface')));
    }

    /**
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileMap
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileFromTemplateMapResolver
     */
    public function testCompileFromMapResolver()
    {
        $mapResolver = $this->getMock('Zend\View\Resolver\TemplateMapResolver');
        $mapResolver
            ->expects($this->any())
            ->method('getMap')
            ->will($this->returnValue(array('a' => 'b', 'c' => 'd')));

        $map = $this->compiler->compileMap($mapResolver);

        $this->assertCount(2, $map);
        $this->assertSame('b', $map['a']);
        $this->assertSame('d', $map['c']);
    }

    /**
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileMap
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileFromTemplatePathStack
     */
    public function testCompileFromTemplatePathStack()
    {
        $templatePathStack = $this->getMock('Zend\View\Resolver\TemplatePathStack');
        $paths = $this->getMock('Zend\StdLib\SplStack');
        $paths
            ->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array(__DIR__ . '/_files/subdir2', __DIR__ . '/_files/subdir1')));

        $templatePathStack
            ->expects($this->any())
            ->method('getPaths')
            ->will($this->returnValue($paths));
        $templatePathStack
            ->expects($this->any())
            ->method('resolve')
            ->will(
                $this->returnCallback(
                    function ($name) {
                        $keys = array(
                            'template2'       => __DIR__ . '/_files/subdir2/template2.phtml',
                            'template3'       => false,
                            'valid/template4' => __DIR__ . '/_files/subdir1/valid/template4.phtml',
                        );

                        return $keys[$name];
                    }
                )
            );

        $map = $this->compiler->compileMap($templatePathStack);

        $this->assertCount(2, $map);

        $template2 = realpath(__DIR__ . '/_files/subdir2/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        $this->assertInternalType('string', $template2);
        $this->assertInternalType('string', $template4);

        $this->assertSame($template2, $map['template2']);
        $this->assertSame($template4, $map['valid/template4']);
    }

    /**
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileMap
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileFromAggregateResolver
     */
    public function testCompileFromAggregateResolver()
    {
        $aggregateResolver = $this->getMock('Zend\View\Resolver\AggregateResolver');
        $mapResolver1 = $this->getMock('Zend\View\Resolver\TemplateMapResolver');
        $mapResolver1
            ->expects($this->any())
            ->method('getMap')
            ->will($this->returnValue(array('a' => 'a-value', 'b' => 'b-value')));
        $mapResolver2 = $this->getMock('Zend\View\Resolver\TemplateMapResolver');
        $mapResolver2
            ->expects($this->any())
            ->method('getMap')
            ->will($this->returnValue(array('c' => 'c-value', 'd' => 'd-value')));
        $mapResolver3 = $this->getMock('Zend\View\Resolver\TemplateMapResolver');
        $mapResolver3
            ->expects($this->any())
            ->method('getMap')
            ->will($this->returnValue(array('a' => 'override-a-value', 'd' => 'override-d-value', 'e' => 'e-value')));

        $iterator = $this->getMock('Zend\Stdlib\PriorityQueue');
        $iterator
            ->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array($mapResolver1, $mapResolver2, $mapResolver3)));
        $aggregateResolver
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue($iterator));

        $map = $this->compiler->compileMap($aggregateResolver);

        $this->assertCount(5, $map);
        $this->assertSame('override-a-value', $map['a']);
        $this->assertSame('b-value', $map['b']);
        $this->assertSame('c-value', $map['c']);
        $this->assertSame('override-d-value', $map['d']);
        $this->assertSame('e-value', $map['e']);
    }
}
