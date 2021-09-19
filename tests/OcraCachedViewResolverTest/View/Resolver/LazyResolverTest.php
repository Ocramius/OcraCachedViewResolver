<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver;

use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\LazyResolver}
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\View\Resolver\LazyResolver
 */
class LazyResolverTest extends TestCase
{
    private MockObject $resolverInstantiator;

    /** @var ResolverInterface&MockObject */
    private $realResolver;

    /** @var RendererInterface&MockObject */
    private $renderer;

    private LazyResolver $lazyResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolverInstantiator = $this->getMockBuilder(stdClass::class)->addMethods(['__invoke'])->getMock();
        $this->realResolver         = $this->createMock(ResolverInterface::class);
        $this->renderer             = $this->createMock(RendererInterface::class);
        /** @psalm-var callable(): ResolverInterface $resolverInstantiator */
        $resolverInstantiator = $this->resolverInstantiator;

        $this->lazyResolver = new LazyResolver($resolverInstantiator);
    }

    public function testResolve(): void
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
            ->with('view-name', $this->renderer)
            ->willReturn('path/to/script');

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
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
            ->method('resolve')
            ->with('view-name', null)
            ->willReturn('path/to/script');

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name'));
    }

    public function testRealResolverNotCreatedIfNotNeeded(): void
    {
        $this->resolverInstantiator->expects(self::never())->method('__invoke');

        /** @psalm-var callable(): ResolverInterface $resolverInstantiator */
        $resolverInstantiator = $this->resolverInstantiator;

        new LazyResolver($resolverInstantiator);
    }

    public function testResolveCausesRealResolverInstantiationOnlyOnce(): void
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($this->realResolver);
        $this
            ->realResolver
            ->expects(self::exactly(2))
            ->method('resolve')
            ->with('view-name', $this->renderer)
            ->willReturn('path/to/script');

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
    }
}
