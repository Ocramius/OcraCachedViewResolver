<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver;

use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;
use OcraCachedViewResolver\Module;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for {@see \OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory}
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory
 */
class CompiledMapResolverDelegatorFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private $locator;

    private MockObject $callback;

    /** @var StorageInterface&MockObject */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator  = $this->createMock(ContainerInterface::class);
        $this->callback = $this->getMockBuilder(stdClass::class)->addMethods(['__invoke'])->getMock();
        $this->cache    = $this->createMock(StorageInterface::class);

        $this->locator->method('get')->will(self::returnValueMap([
            [
                'Config',
                [
                    Module::CONFIG => [
                        Module::CONFIG_CACHE_KEY     => 'key-name',
                        Module::CONFIG_CACHE_SERVICE => 'cache_name',
                    ],
                ],
            ],
            [
                'cache_name',
                $this->cache,
            ],
        ]));
    }

    public function testCreateServiceWithExistingCachedTemplateMap(): void
    {
        $this
            ->cache
            ->expects(self::once())
            ->method('getItem')
            ->with('key-name')
            ->willReturn(['foo' => 'bar']);

        $this->callback->expects(self::never())->method('__invoke');

        /** @var callable(): ResolverInterface $callback */
        $callback = $this->callback;
        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->__invoke($this->locator, 'resolver', $callback);

        self::assertInstanceOf(AggregateResolver::class, $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        self::assertInstanceOf(LazyResolver::class, $resolvers[0]);
        self::assertInstanceOf(CachingMapResolver::class, $resolvers[1]);

        self::assertSame('bar', $resolver->resolve('foo'));
    }

    public function testCreateServiceWithEmptyCachedTemplateMap(): void
    {
        $realResolver = new TemplateMapResolver(['bar' => 'baz']);

        $this->cache->expects(self::once())->method('getItem')->with('key-name')->willReturn(null);
        $this->cache->expects(self::once())->method('setItem')->with('key-name', ['bar' => 'baz']);
        $this->callback->expects(self::once())->method('__invoke')->willReturn($realResolver);

        /** @var callable(): ResolverInterface $callback */
        $callback = $this->callback;
        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->__invoke($this->locator, 'resolver', $callback);

        self::assertInstanceOf(AggregateResolver::class, $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        self::assertInstanceOf(LazyResolver::class, $resolvers[0]);
        self::assertInstanceOf(CachingMapResolver::class, $resolvers[1]);

        self::assertSame('baz', $resolver->resolve('bar'));
    }
}
