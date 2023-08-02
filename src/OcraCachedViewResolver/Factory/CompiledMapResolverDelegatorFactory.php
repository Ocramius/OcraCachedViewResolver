<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\Factory;

use Interop\Container\ContainerInterface;
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
 *
 * @see Module
 *
 * @psalm-import-type OcraCachedViewResolverConfiguration from Module
 */
final class CompiledMapResolverDelegatorFactory implements DelegatorFactoryInterface
{
    private const LAZY_REAL_RESOLVER_PRIORITY = 50;
    private const CACHED_RESOLVER_PRIORITY    = 100;

    /** {@inheritDoc} */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): ResolverInterface {
        /** @psalm-var OcraCachedViewResolverConfiguration $allConfig */
        $allConfig = $container->get('Config');
        $config    = $allConfig[Module::CONFIG];
        $cache     = $container->get($config[Module::CONFIG_CACHE_SERVICE]);
        assert($cache instanceof StorageInterface);

        $resolver = new AggregateResolver();

//phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
//phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.NoAssignment
        /** @var callable(): ResolverInterface $callback */
//phpcs:enable
        $resolver->attach(new LazyResolver($callback), self::LAZY_REAL_RESOLVER_PRIORITY);
        $resolver->attach(
            new CachingMapResolver($cache, $config[Module::CONFIG_CACHE_KEY], $callback),
            self::CACHED_RESOLVER_PRIORITY,
        );

        return $resolver;
    }
}
