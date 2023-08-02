<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolverTest\Factory\Asset\InvokableObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\CachingMapResolver}
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\View\Resolver\CachingMapResolver
 */
class CachingMapResolverTest extends TestCase
{
    /** @var InvokableObject&MockObject */
    private MockObject $resolverInstantiator;

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

        $this->resolverInstantiator = $this->createMock(InvokableObject::class);
        $this->realResolver         = $this->createMock(TemplateMapResolver::class);
        $this->renderer             = $this->createMock(RendererInterface::class);
        $this->cache                = $this->createMock(StorageInterface::class);
        $this->cachingMapResolver   = new CachingMapResolver(
            $this->cache,
            $this->cacheKey,
            $this->resolverInstantiator,
        );

        $this
            ->realResolver
            ->expects(self::any())
            ->method('getMap')
            ->willReturn(['view-name' => 'path/to/script']);
    }

    public function testResolverCacheIsPopulatedOnResolve(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($this->realResolver);
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
            ->willReturn($this->realResolver);
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
            ->willReturn(['view-name' => 'path/to/cached/script']);

        self::assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertSame('path/to/cached/script', $this->cachingMapResolver->resolve('view-name', $this->renderer));
        self::assertFalse($this->cachingMapResolver->resolve('unknown-view-name', $this->renderer));
    }

    public function testResolveWithoutRenderer(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($this->realResolver);
        $this
            ->realResolver
            ->expects(self::any())
            ->method('resolve')
            ->with('view-name', null)
            ->willReturn('path/to/script');

        self::assertSame('path/to/script', $this->cachingMapResolver->resolve('view-name'));
    }
}
