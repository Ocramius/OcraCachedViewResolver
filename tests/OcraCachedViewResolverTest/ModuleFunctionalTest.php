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

use PHPUnit_Framework_TestCase;
use UnexpectedValueException;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Functional test to verify that the module initializes services correctly
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class ModuleFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Zend\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject;
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
        $this->serviceManager->setService(
            'ApplicationConfig',
            array(
                'modules' => array('OcraCachedViewResolver'),
                'module_listener_options' => array(
                    'config_glob_paths'    => array(
                        __DIR__ . '/../testing.config.php',
                    ),
                ),
            )
        );

        /* @var $moduleManager \Zend\ModuleManager\ModuleManager */
        $moduleManager = $this->serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        $this->originalResolver = new AggregateResolver();
        $mapResolver            = $this->getMock('Zend\View\Resolver\TemplateMapResolver');
        $this->fallbackResolver = $this->getMock('Zend\View\Resolver\ResolverInterface');

        $mapResolver->expects($this->once())->method('getMap')->will($this->returnValue(array('a' => 'b')));

        $this->originalResolver->attach($mapResolver, 10);
        $this->originalResolver->attach($this->fallbackResolver, 5);
        $this->serviceManager->setService(
            'OcraCachedViewResolver\\Resolver\\OriginalResolver',
            $this->originalResolver
        );
    }

    public function testDefinedServices()
    {
        $this->assertSame(
            $this->originalResolver,
            $this->serviceManager->get('Zend\\View\\Resolver\\AggregateResolver')
        );

        $this->assertInstanceOf(
            'Zend\\Cache\\Storage\\StorageInterface',
            $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache')
        );

        /* @var $resolver \Zend\View\Resolver\AggregateResolver */
        $resolver = $this->serviceManager->get('OcraCachedViewResolver\\Resolver\\CompiledMapResolver');

        $this->assertInstanceOf('Zend\View\Resolver\AggregateResolver', $resolver);
        $this->assertSame($resolver, $this->serviceManager->get('ViewResolver'));

        foreach ($resolver->getIterator() as $previousResolver) {
            if ($previousResolver instanceof AggregateResolver) {
                $this->assertSame($this->originalResolver, $previousResolver);
            } elseif ($previousResolver instanceof TemplateMapResolver) {
                $this->assertNotSame($previousResolver, $this->originalResolver);
                $this->assertSame(array('a' => 'b'), $previousResolver->getMap());
            } else {
                throw new UnexpectedValueException(
                    sprintf('Found unexpected resolver of type "%s"', get_class($previousResolver))
                );
            }
        }
    }

    public function testCachesResolvedTemplates()
    {
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache = $this->serviceManager->get('OcraCachedViewResolver\\Cache\\ResolverCache');

        $this->assertFalse($cache->hasItem('cached_template_map'));
        $this->serviceManager->create('ViewResolver');
        $this->assertSame(array('a' => 'b'), $cache->getItem('cached_template_map'));
        $this->serviceManager->create('ViewResolver');
    }

    public function testFallbackResolverCall()
    {
        /* @var $resolver \Zend\View\Resolver\TemplateMapResolver */
        $resolver = $this->serviceManager->get('OcraCachedViewResolver\\Resolver\\CompiledMapResolver');

        $this
            ->fallbackResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('fallback.phtml')
            ->will($this->returnValue('fallback-path.phtml'));

        $this->assertSame('fallback-path.phtml', $resolver->resolve('fallback.phtml'));
    }
}
