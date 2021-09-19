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

use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use stdClass;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

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
class LazyResolverTest extends TestCase
{
    /**
     * @var callable&MockObject
     */
    private $resolverInstantiator;

    /**
     * @var \Laminas\View\Resolver\ResolverInterface&MockObject
     */
    private $realResolver;

    /**
     * @var \Laminas\View\Renderer\RendererInterface&MockObject
     */
    private $renderer;

    /**
     * @var LazyResolver
     */
    private $lazyResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolverInstantiator = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $this->realResolver         = $this->createMock(ResolverInterface::class);
        $this->renderer             = $this->createMock(RendererInterface::class);
        $this->lazyResolver         = new LazyResolver($this->resolverInstantiator);
    }

    public function testResolve()
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
            ->with('view-name', $this->renderer)
            ->will(self::returnValue('path/to/script'));

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
    }

    public function testResolveWithoutRenderer()
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue($this->realResolver));
        $this
            ->realResolver
            ->method('resolve')
            ->with('view-name', null)
            ->will(self::returnValue('path/to/script'));

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name'));
    }

    public function testRealResolverNotCreatedIfNotNeeded()
    {
        $this->resolverInstantiator->expects(self::never())->method('__invoke');

        new LazyResolver($this->resolverInstantiator);
    }

    public function testResolveCausesRealResolverInstantiationOnlyOnce()
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue($this->realResolver));
        $this
            ->realResolver
            ->expects(self::exactly(2))
            ->method('resolve')
            ->with('view-name', $this->renderer)
            ->will(self::returnValue('path/to/script'));

        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
        self::assertSame('path/to/script', $this->lazyResolver->resolve('view-name', $this->renderer));
    }

    public function testLazyResolverRefusesNonCallableInstantiator()
    {
        $this->expectException(InvalidResolverInstantiatorException::class);

        new LazyResolver($this);
    }

    public function testLazyResolverRefusesInvalidRealResolver()
    {
        $this
            ->resolverInstantiator
            ->expects(self::once())
            ->method('__invoke')
            ->will(self::returnValue(null));

        $lazyResolver = new LazyResolver($this->resolverInstantiator);

        $this->expectException(InvalidResolverInstantiatorException::class);

        $lazyResolver->resolve('foo');
    }
}
