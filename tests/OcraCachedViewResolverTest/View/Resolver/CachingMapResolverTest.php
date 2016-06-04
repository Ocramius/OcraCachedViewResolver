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

use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use PHPUnit_Framework_TestCase;
use stdClass;
use Zend\Cache\Storage\StorageInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\CachingMapResolver}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * @covers \OcraCachedViewResolver\View\Resolver\CachingMapResolver
 */
class CachingMapResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var callable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverInstantiator;

    /**
     * @var TemplateMapResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $realResolver;

    /**
     * @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    /**
     * @var StorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey = 'cache-key';

    /**
     * @var CachingMapResolver
     */
    private $cachingMapResolver;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resolverInstantiator = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $this->realResolver         = $this->createMock(TemplateMapResolver::class);
        $this->renderer             = $this->createMock(RendererInterface::class);
        $this->cache                = $this->createMock(StorageInterface::class);
        $this->cachingMapResolver   = new CachingMapResolver(
            $this->cache,
            $this->cacheKey,
            $this->resolverInstantiator
        );

        $this
            ->realResolver
            ->expects($this->any())
            ->method('getMap')
            ->will($this->returnValue(['view-name' => 'path/to/script']));
    }

    public function testResolverCacheIsPopulatedOnResolve()
    {
        $this
            ->resolverInstantiator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($this->realResolver));
        $this
            ->cache
            ->expects($this->once())
            ->method('getItem')
            ->with($this->cacheKey);
        $this
            ->cache
            ->expects($this->once())
            ->method('setItem')
            ->with($this->cacheKey, ['view-name' => 'path/to/script']);

        $this->assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
    }

    public function testResolvingMultipleTimesDoesNotHitResolverInstantiatorOrCache()
    {
        $this
            ->resolverInstantiator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($this->realResolver));
        $this
            ->cache
            ->expects($this->once())
            ->method('getItem')
            ->with($this->cacheKey);
        $this
            ->cache
            ->expects($this->once())
            ->method('setItem')
            ->with($this->cacheKey, ['view-name' => 'path/to/script']);

        $this->assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        $this->assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        $this->assertFalse($this->cachingMapResolver->resolve('unknown-view-name', $this->renderer));
    }

    public function testResolvingWithNonEmptyCacheWillNotHitResolverInstantiatorOrWriteToCache()
    {
        $this->resolverInstantiator->expects($this->never())->method('__invoke');
        $this->cache->expects($this->never())->method('setItem');

        $this
            ->cache
            ->expects($this->once())
            ->method('getItem')
            ->with($this->cacheKey)
            ->will($this->returnValue(['view-name' => 'path/to/cached/script']));

        $this->assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        $this->assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        $this->assertFalse($this->cachingMapResolver->resolve('unknown-view-name', $this->renderer));
    }

    /**
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::resolve
     */
    public function testResolveWithoutRenderer()
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
            ->with('view-name', null)
            ->will($this->returnValue('path/to/script'));

        $this->assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name'));
    }

    /**
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::resolve
     */
    public function testLazyResolverRefusesInvalidRealResolver()
    {
        $this
            ->resolverInstantiator
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(null));

        $cachingMapResolver = new CachingMapResolver($this->cache, $this->cacheKey, $this->resolverInstantiator);

        $this->setExpectedException(InvalidResolverInstantiatorException::class);

        $cachingMapResolver->resolve('foo');
    }
}
