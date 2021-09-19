<?php

declare(strict_types=1);

namespace OcraCachedViewResolver;

use Laminas\Cache\Storage\Adapter\Apc;
use Laminas\Cache\Storage\Adapter\BlackHole;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use OcraCachedViewResolver\Factory\CacheFactory;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;

/**
 * OcraCachedViewResolver module
 */
final class Module implements ConfigProviderInterface
{
    /**
     * Name of the cache namespace where configs for this module are wrapped
     */
    public const CONFIG = 'ocra_cached_view_resolver';

    /**
     * Name of the config key referencing the array with cache definitions to be passed to
     * the {@see \Laminas\Cache\StorageFactory}
     */
    public const CONFIG_CACHE_DEFINITION = 'cache';

    /**
     * Name of the config key referencing the cache service to be used when storing the cached map
     */
    public const CONFIG_CACHE_SERVICE = 'cache_service';

    /**
     * Name of the config key referencing the cache key to be used when storing the cached map
     */
    public const CONFIG_CACHE_KEY = 'cached_template_map_key';

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            self::CONFIG => [
                self::CONFIG_CACHE_DEFINITION => ['adapter' => Apc::class],
                self::CONFIG_CACHE_KEY     => 'cached_template_map',
                self::CONFIG_CACHE_SERVICE => 'OcraCachedViewResolver\\Cache\\DummyCache',
            ],
            'service_manager' => [
                'invokables' => ['OcraCachedViewResolver\\Cache\\DummyCache' => BlackHole::class],
                'factories'  => ['OcraCachedViewResolver\\Cache\\ResolverCache' => CacheFactory::class],
                'delegators' => [
                    'ViewResolver' => [
                        CompiledMapResolverDelegatorFactory::class => CompiledMapResolverDelegatorFactory::class,
                    ],
                ],
            ],
        ];
    }
}
