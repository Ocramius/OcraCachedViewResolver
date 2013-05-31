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
use Zend\View\Resolver\TemplatePathStack;

/**
 * Template map compiler functional tests
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class TemplateMapCompilerFunctionalTest extends PHPUnit_Framework_TestCase
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
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileFromTemplatePathStack
     */
    public function testCompileFromTemplatePathStack()
    {
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/_files/subdir1');
        $resolver->addPath(__DIR__ . '/_files/subdir2');

        $template2 = realpath(__DIR__ . '/_files/subdir2/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        $this->assertInternalType('string', $template2);
        $this->assertInternalType('string', $template4);

        $this->assertSame(
            array(
                'template2'       => $template2,
                'valid/template4' => $template4,
            ),
            $this->compiler->compileMap($resolver)
        );
    }

    /**
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileMap
     * @covers \OcraCachedViewResolver\Compiler\TemplateMapCompiler::compileFromTemplatePathStack
     */
    public function testCompileFromTemplatePathStackWithDifferentPaths()
    {
        $template2 = realpath(__DIR__ . '/_files/subdir1/template2.phtml');
        $template4 = realpath(__DIR__ . '/_files/subdir1/valid/template4.phtml');

        $this->assertInternalType('string', $template2);
        $this->assertInternalType('string', $template4);

        // inverse directory order
        $resolver = new TemplatePathStack();
        $resolver->addPath(__DIR__ . '/_files/subdir2');
        $resolver->addPath(__DIR__ . '/_files/subdir1');

        $map = $this->compiler->compileMap($resolver);

        $this->assertCount(2, $map);
        $this->assertSame($template2, $map['template2']);
        $this->assertSame($template4, $map['valid/template4']);
    }
}
