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
    public function testCreateServiceWithExistingCachedTemplateMap()
    {
        /* @var $locator \Zend\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator  = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        /* @var $callback callable|\PHPUnit_Framework_MockObject_MockObject */
        $callback = $this->getMock('stdLib', array('__invoke'));
        /* @var $cache \Zend\Cache\Storage\StorageInterface|\PHPUnit_Framework_MockObject_MockObject */
        $cache    = $this->getMock('Zend\Cache\Storage\StorageInterface');

        $cache
            ->expects($this->once())
            ->method('getItem')
            ->with('key-name')
            ->will($this->returnValue(array('foo' => 'bar')));

        $callback->expects($this->never())->method('__invoke');

        $locator->expects($this->any())->method('get')->will($this->returnValueMap(array(
            array('Config', array('ocra_cached_view_resolver' => array('cached_template_map_key' => 'key-name'))),
            array('OcraCachedViewResolver\\Cache\\ResolverCache', $cache),
        )));

        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->createDelegatorWithName($locator, 'resolver', 'resolver', $callback);

        $this->assertInstanceOf('Zend\View\Resolver\AggregateResolver', $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        $this->assertInstanceOf('OcraCachedViewResolver\View\Resolver\LazyResolver', $resolvers[0]);
        $this->assertInstanceOf('Zend\View\Resolver\TemplateMapResolver', $resolvers[1]);

        $this->assertSame('bar', $resolver->resolve('foo'));
    }
}
