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

use Interop\Container\ContainerInterface;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;
use OcraCachedViewResolver\Module;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplateMapResolver;

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
class CompiledMapResolverDelegatorFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    private $locator;

    /**
     * @var callable&MockObject
     */
    private $callback;

    /**
     * @var StorageInterface&MockObject
     */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator  = $this->createMock(ContainerInterface::class);
        $this->callback = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
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

    public function testCreateServiceWithExistingCachedTemplateMap()
    {
        $this
            ->cache
            ->expects(self::once())
            ->method('getItem')
            ->with('key-name')
            ->will(self::returnValue(['foo' => 'bar']));

        $this->callback->expects(self::never())->method('__invoke');

        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->__invoke($this->locator, 'resolver', $this->callback);

        self::assertInstanceOf(AggregateResolver::class, $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        self::assertInstanceOf(LazyResolver::class, $resolvers[0]);
        self::assertInstanceOf(CachingMapResolver::class, $resolvers[1]);

        self::assertSame('bar', $resolver->resolve('foo'));
    }

    public function testCreateServiceWithEmptyCachedTemplateMap()
    {
        $realResolver = new TemplateMapResolver(['bar' => 'baz']);

        $this->cache->expects(self::once())->method('getItem')->with('key-name')->will(self::returnValue(null));
        $this->cache->expects(self::once())->method('setItem')->with('key-name', ['bar' => 'baz']);
        $this->callback->expects(self::once())->method('__invoke')->will(self::returnValue($realResolver));

        $factory  = new CompiledMapResolverDelegatorFactory();
        $resolver = $factory->__invoke($this->locator, 'resolver', $this->callback);

        self::assertInstanceOf(AggregateResolver::class, $resolver);

        $resolvers = $resolver->getIterator()->toArray();

        self::assertInstanceOf(LazyResolver::class, $resolvers[0]);
        self::assertInstanceOf(CachingMapResolver::class, $resolvers[1]);

        self::assertSame('baz', $resolver->resolve('bar'));
    }
}
