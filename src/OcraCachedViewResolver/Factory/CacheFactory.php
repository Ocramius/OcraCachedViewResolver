<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Cache\Exception\InvalidArgumentException;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use OcraCachedViewResolver\Module;

/**
 * Factory responsible of building a {@see \Laminas\Cache\Storage\StorageInterface}
 * for the resolver
 *
 * @see Module
 *
 * @psalm-import-type OcraCachedViewResolverConfiguration from Module
 */
final class CacheFactory
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container): StorageInterface
    {
        /** @psalm-var OcraCachedViewResolverConfiguration $config */
        $config = $container->get('Config');

        return StorageFactory::factory($config[Module::CONFIG][Module::CONFIG_CACHE_DEFINITION]);
    }
}
