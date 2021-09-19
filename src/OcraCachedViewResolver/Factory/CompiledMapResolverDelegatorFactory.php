<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use OcraCachedViewResolver\Module;
use OcraCachedViewResolver\View\Resolver\CachingMapResolver;
use OcraCachedViewResolver\View\Resolver\LazyResolver;

use function assert;

/**
 * Factory responsible of building a {@see \Laminas\View\Resolver\TemplateMapResolver}
 * from cached template definitions
 */
final class CompiledMapResolverDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     *
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): ResolverInterface {
        $config = $container->get('Config')[Module::CONFIG];
        $cache  = $container->get($config[Module::CONFIG_CACHE_SERVICE]);
        assert($cache instanceof StorageInterface);

        $resolver = new AggregateResolver();

        $resolver->attach(new LazyResolver($callback), 50);
        $resolver->attach(new CachingMapResolver($cache, $config[Module::CONFIG_CACHE_KEY], $callback), 100);

        return $resolver;
    }
}
