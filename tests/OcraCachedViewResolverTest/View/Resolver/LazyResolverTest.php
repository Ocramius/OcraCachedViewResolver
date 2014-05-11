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

namespace OcraCachedViewResolverTest\View\Resolver;

use PHPUnit_Framework_TestCase;
use OcraCachedViewResolver\View\Resolver\LazyResolver;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\LazyResolver}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver
 */
class LazyResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var callable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverInstantiator;

    /**
     * @var \Zend\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $realResolver;

    /**
     * @var \Zend\View\Renderer\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    /**
     * @var LazyResolver
     */
    private $lazyResolver;

    /**
     * {@inheritDoc}
     *
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::__construct
     */
    protected function setUp()
    {
        $this->resolverInstantiator = $this->getMock('stdClass', array('__invoke'));
        $this->realResolver         = $this->getMock('Zend\View\Resolver\ResolverInterface');
        $this->renderer             = $this->getMock('Zend\View\Renderer\RendererInterface');

        $this->lazyResolver = new LazyResolver($this->resolverInstantiator);
    }

    /**
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::resolve
     */
    public function testResolve()
    {
        $this
            ->resolverInstantiator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($this->realResolver));
        $this
            ->realResolver
            ->expects($this->any())
            ->method('resolve')
            ->with('view-name', $this->renderer)
            ->will($this->returnValue('path/to/script'));
    }
}
