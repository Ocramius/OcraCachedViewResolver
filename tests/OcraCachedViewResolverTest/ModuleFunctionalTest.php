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

namespace OcraCachedViewResolverTest;

use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit\Framework\TestCase;
use Zend\Cache\Storage\StorageInterface;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Functional test to verify that the module initializes services correctly
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 *
 * @coversNothing
 */
class ModuleFunctionalTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var AggregateResolver|\Zend\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $originalResolver;

    /**
     * @var \Zend\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $fallbackResolver;

    /**
     * {@inheritDoc}
     *
     * @throws \PHPUnit_Framework_Exception
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function setUp()
    {
        $this->serviceManager = new ServiceManager();

        (new ServiceManagerConfig())->configureServiceManager($this->serviceManager);

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'ApplicationConfig',
            [
                'modules' => [
                    'Zend\Router',
                    'OcraCachedViewResolver',
                ],
                'module_listener_options' => [
                    'config_glob_paths'    => [
                        __DIR__ . '/../testing.config.php',
                    ],
                ],
            ]
        );

        /* @var $moduleManager \Zend\ModuleManager\ModuleManager */
        $moduleManager = $this->serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        $this->originalResolver = new AggregateResolver();
        /* @var $mapResolver TemplateMapResolver|\PHPUnit_Framework_MockObject_MockObject */
        $mapResolver            = $this->createMock(TemplateMapResolver::class);
        $this->fallbackResolver = $this->createMock(ResolverInterface::class);

        $mapResolver->expects(self::any())->method('getMap')->will(self::returnValue(['a' => 'b']));

        $this->originalResolver->attach($mapResolver, 10);
        $this->originalResolver->attach($this->fallbackResolver, 5);

        $originalResolver = $this->originalResolver;
        $this->serviceManager->setFactory(
            'ViewResolver',
            function () use ($originalResolver) {
                return $originalResolver;
            }
        );
    }

    public function testDefinedServices()
    {
        self::assertInstanceOf(
            StorageInterface::class,
            $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache')
        );

        /* @var $resolver \Zend\View\Resolver\AggregateResolver */
        $resolver = $this->serviceManager->get('ViewResolver');

        self::assertInstanceOf(AggregateResolver::class, $resolver);
        self::assertSame($resolver, $this->serviceManager->get('ViewResolver'));

        foreach ($resolver->getIterator() as $previousResolver) {
            self::assertThat(
                $previousResolver,
                self::logicalOr(
                    self::isInstanceOf(CachingMapResolver::class),
                    self::isInstanceOf(LazyResolver::class)
                )
            );
        }
    }

    public function testCachesResolvedTemplates()
    {
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache = $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache');

        self::assertFalse($cache->hasItem('testing_cache_key'));

        /* @var $resolver AggregateResolver */
        $resolver = $this->serviceManager->build('ViewResolver');

        self::assertFalse($cache->hasItem('testing_cache_key'));
        self::assertSame('b', $resolver->resolve('a'));
        self::assertTrue($cache->hasItem('testing_cache_key'));
        self::assertSame(['a' => 'b'], $cache->getItem('testing_cache_key'));
        $this->serviceManager->build('ViewResolver');
    }

    public function testFallbackResolverCall()
    {
        /* @var $resolver \Zend\View\Resolver\TemplateMapResolver */
        $resolver = $this->serviceManager->get('ViewResolver');

        $this
            ->fallbackResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('fallback.phtml')
            ->will(self::returnValue('fallback-path.phtml'));

        self::assertSame('fallback-path.phtml', $resolver->resolve('fallback.phtml'));
    }
}
