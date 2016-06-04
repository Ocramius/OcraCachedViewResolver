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

use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
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
class ModuleFunctionalTest extends PHPUnit_Framework_TestCase
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
     */
    public function setUp()
    {
        $this->serviceManager = new ServiceManager(new ServiceManagerConfig());

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'ApplicationConfig',
            [
                'modules' => ['OcraCachedViewResolver'],
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

        $mapResolver->expects($this->any())->method('getMap')->will($this->returnValue(['a' => 'b']));

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
        $this->assertInstanceOf(
            'Zend\\Cache\\Storage\\StorageInterface',
            $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache')
        );

        /* @var $resolver \Zend\View\Resolver\AggregateResolver */
        $resolver = $this->serviceManager->get('ViewResolver');

        $this->assertInstanceOf(AggregateResolver::class, $resolver);
        $this->assertSame($resolver, $this->serviceManager->get('ViewResolver'));

        foreach ($resolver->getIterator() as $previousResolver) {
            $this->assertThat(
                $previousResolver,
                $this->logicalOr(
                    $this->isInstanceOf(CachingMapResolver::class),
                    $this->isInstanceOf(LazyResolver::class)
                )
            );
        }
    }

    public function testCachesResolvedTemplates()
    {
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache = $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache');

        $this->assertFalse($cache->hasItem('testing_cache_key'));

        /* @var $resolver AggregateResolver */
        $resolver = $this->serviceManager->create('ViewResolver');

        $this->assertFalse($cache->hasItem('testing_cache_key'));
        $this->assertSame('b', $resolver->resolve('a'));
        $this->assertTrue($cache->hasItem('testing_cache_key'));
        $this->assertSame(['a' => 'b'], $cache->getItem('testing_cache_key'));
        $this->serviceManager->create('ViewResolver');
    }

    public function testFallbackResolverCall()
    {
        /* @var $resolver \Zend\View\Resolver\TemplateMapResolver */
        $resolver = $this->serviceManager->get('ViewResolver');

        $this
            ->fallbackResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('fallback.phtml')
            ->will($this->returnValue('fallback-path.phtml'));

        $this->assertSame('fallback-path.phtml', $resolver->resolve('fallback.phtml'));
    }
}
