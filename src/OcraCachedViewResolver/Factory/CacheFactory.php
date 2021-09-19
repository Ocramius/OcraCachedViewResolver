<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Laminas\Cache\Exception\InvalidArgumentException;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use OcraCachedViewResolver\Module;

/**
 * Factory responsible of building a {@see \Laminas\Cache\Storage\StorageInterface}
 * for the resolver
 */
final class CacheFactory
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function __invoke(ContainerInterface $container): StorageInterface
    {
        $config = $container->get('Config');

        return StorageFactory::factory($config[Module::CONFIG][Module::CONFIG_CACHE_DEFINITION]);
    }
}
