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

use OcraCachedViewResolver\Factory\CacheFactory;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;
use PHPUnit_Framework_TestCase;
use OcraCachedViewResolver\View\Resolver\LazyResolver;

/**
 * Tests for {@see \OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * @covers \OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory
 */
class CompiledMapResolverDelegatorFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locator;

    /**
     * @var callable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $callback;

    /**
     * @var \Zend\Cache\Storage\StorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        /* @var $locator */
        $this->locator  = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $this->callback = $this->getMock('stdLib', array('__invoke'));
        $this->cache    = $this->getMock('Zend\Cache\Storage\StorageInterface');

        $this->locator->expects($this->any())->method('get')->will($this->returnValueMap(array(
            array('Config', array('ocra_cached_view_resolver' => array('cached_template_map_key' => 'key-name'))),
            array('OcraCachedViewResolver\\Cache\\ResolverCache', $this->cache),
        )));
    }

    public function testCreateServiceWithExistingCachedTemplateMap()
    {
        $this
            ->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('key-name')
            ->will($this->returnValue(array('foo' => 'bar')));

        $this->callback->expects($this->never())->method('__invoke');

        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->createDelegatorWithName($this->locator, 'resolver', 'resolver', $this->callback);

        $this->assertInstanceOf('Zend\View\Resolver\AggregateResolver', $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        $this->assertInstanceOf('OcraCachedViewResolver\View\Resolver\LazyResolver', $resolvers[0]);
        $this->assertInstanceOf('Zend\View\Resolver\TemplateMapResolver', $resolvers[1]);

        $this->assertSame('bar', $resolver->resolve('foo'));
    }
}
