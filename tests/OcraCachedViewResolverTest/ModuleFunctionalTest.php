<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest;

use Laminas\Cache\Storage\Adapter\Memory;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * Functional test to verify that the module initializes services correctly
 *
 * @group Functional
 * @coversNothing
 */
class ModuleFunctionalTest extends TestCase
{
    protected ServiceManager $serviceManager;

    /** @var AggregateResolver ; */
    protected AggregateResolver $originalResolver;

    /** @var ResolverInterface&MockObject ; */
    protected $fallbackResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceManager = new ServiceManager();

        (new ServiceManagerConfig())->configureServiceManager($this->serviceManager);

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'ApplicationConfig',
            [
                'modules' => [
                    'Laminas\Router',
                    'OcraCachedViewResolver',
                ],
                'module_listener_options' => [
                    'config_glob_paths'    => [
                        __DIR__ . '/../testing.config.php',
                    ],
                ],
            ],
        );

        $moduleManager = $this->serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);
        $moduleManager->loadModules();

        $this->originalResolver = new AggregateResolver();
        $mapResolver            = $this->createMock(TemplateMapResolver::class);
        $this->fallbackResolver = $this->createMock(ResolverInterface::class);

        $mapResolver->method('getMap')->willReturn(['a' => 'b']);

        $this->originalResolver->attach($mapResolver, 10);
        $this->originalResolver->attach($this->fallbackResolver, 5);

        $originalResolver = $this->originalResolver;
        $this->serviceManager->setFactory(
            'ViewResolver',
            static function () use ($originalResolver) {
                return $originalResolver;
            },
        );
    }

    public function testDefinedServices(): void
    {
        $resolver = $this->serviceManager->get('ViewResolver');
        assert($resolver instanceof AggregateResolver);

        self::assertInstanceOf(AggregateResolver::class, $resolver);
        self::assertSame($resolver, $this->serviceManager->get('ViewResolver'));

        foreach ($resolver->getIterator() as $previousResolver) {
            assert($previousResolver instanceof ResolverInterface);
            self::assertThat(
                $previousResolver,
                self::logicalOr(
                    self::isInstanceOf(CachingMapResolver::class),
                    self::isInstanceOf(LazyResolver::class),
                ),
            );
        }
    }

    public function testCachesResolvedTemplates(): void
    {
        $cache = $this->serviceManager->get(Memory::class);
        assert($cache instanceof StorageInterface);

        self::assertFalse($cache->hasItem('testing_cache_key'));

        $resolver = $this->serviceManager->build('ViewResolver');
        assert($resolver instanceof AggregateResolver);

        self::assertFalse($cache->hasItem('testing_cache_key'));
        self::assertSame('b', $resolver->resolve('a'));
        self::assertTrue($cache->hasItem('testing_cache_key'));
        self::assertSame(['a' => 'b'], $cache->getItem('testing_cache_key'));
        $this->serviceManager->build('ViewResolver');
    }

    public function testFallbackResolverCall(): void
    {
        $resolver = $this->serviceManager->get('ViewResolver');

        self::assertInstanceOf(ResolverInterface::class, $resolver);

        $this
            ->fallbackResolver
            ->expects(self::once())
            ->method('resolve')
            ->with('fallback.phtml')
            ->willReturn('fallback-path.phtml');

        self::assertSame('fallback-path.phtml', $resolver->resolve('fallback.phtml'));
    }
}
