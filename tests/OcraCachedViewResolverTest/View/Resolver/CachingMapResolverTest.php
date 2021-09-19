<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\CachingMapResolver}
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\View\Resolver\CachingMapResolver
 */
class CachingMapResolverTest extends TestCase
{
    /** @var callable&MockObject */
    private $resolverInstantiator;

    /** @var TemplateMapResolver&MockObject */
    private $realResolver;

    /** @var RendererInterface&MockObject */
    private $renderer;

    /** @var StorageInterface&MockObject */
    private $cache;

    private string $cacheKey = 'cache-key';

    private CachingMapResolver $cachingMapResolver;

    protected function setUp(): void
    {
        parent::setUp();

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
            ->expects(self::any())
            ->method('getMap')
            ->will(self::returnValue(['view-name' => 'path/to/script']));
    }

    public function testResolverCacheIsPopulatedOnResolve(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue($this->realResolver));
        $this
            ->cache
            ->expects(self::once())
            ->method('getItem')
            ->with($this->cacheKey);
        $this
            ->cache
            ->expects(self::once())
            ->method('setItem')
            ->with($this->cacheKey, ['view-name' => 'path/to/script']);

        self::assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
    }

    public function testResolvingMultipleTimesDoesNotHitResolverInstantiatorOrCache(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue($this->realResolver));
        $this
            ->cache
            ->expects(self::once())
            ->method('getItem')
            ->with($this->cacheKey);
        $this
            ->cache
            ->expects(self::once())
            ->method('setItem')
            ->with($this->cacheKey, ['view-name' => 'path/to/script']);

        self::assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertFalse($this->cachingMapResolver->resolve('unknown-view-name', $this->renderer));
    }

    public function testResolvingWithNonEmptyCacheWillNotHitResolverInstantiatorOrWriteToCache(): void
    {
        $this->resolverInstantiator->expects(self::never())->method('__invoke');
        $this->cache->expects(self::never())->method('setItem');

        $this
            ->cache
            ->expects(self::once())
            ->method('getItem')
            ->with($this->cacheKey)
            ->will(self::returnValue(['view-name' => 'path/to/cached/script']));

        self::assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertFalse($this->cachingMapResolver->resolve('unknown-view-name', $this->renderer));
    }

    /**
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::resolve
     */
    public function testResolveWithoutRenderer(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue($this->realResolver));
        $this
            ->realResolver
            ->expects(self::any())
            ->method('resolve')
            ->with('view-name', null)
            ->will(self::returnValue('path/to/script'));

        self::assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name'));
    }

    /**
     * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver::resolve
     */
    public function testLazyResolverRefusesInvalidRealResolver(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue(null));

        $cachingMapResolver = new CachingMapResolver($this->cache, $this->cacheKey, $this->resolverInstantiator);

        $this->expectException(InvalidResolverInstantiatorException::class);

        $cachingMapResolver->resolve('foo');
    }
}
