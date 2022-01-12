<?php

declare(strict_types=1);

namespace OcraCachedViewResolver;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;

/**
 * OcraCachedViewResolver module
 *
 * @psalm-type OcraCachedViewResolverConfiguration = array{
 *   ocra_cached_view_resolver: array{
 *     cache: array,
 *     cached_template_map_key: non-empty-string,
 *     cache_service: non-empty-string,
 *   },
 * }
 */
final class Module implements ConfigProviderInterface
{
    /**
     * Name of the cache namespace where configs for this module are wrapped
     */
    public const CONFIG = 'ocra_cached_view_resolver';

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
                self::CONFIG_CACHE_KEY     => 'cached_template_map',
                self::CONFIG_CACHE_SERVICE => 'your-cache-service-id-here',
            ],
            'service_manager' => [
                'delegators' => [
                    'ViewResolver' => [
                        CompiledMapResolverDelegatorFactory::class => CompiledMapResolverDelegatorFactory::class,
                    ],
                ],
            ],
        ];
    }
}
