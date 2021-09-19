<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\View\Resolver;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use OcraCachedViewResolver\Compiler\TemplateMapCompiler;

use function is_array;

final class CachingMapResolver implements ResolverInterface
{
    /** @var callable(): ResolverInterface */
    private $realResolverInstantiator;

    private StorageInterface $cache;

    private string $cacheKey;

    /** @var array<string, string>|null */
    private ?array $map = null;

    /** @param callable(): ResolverInterface $realResolverInstantiator */
    public function __construct(StorageInterface $cache, string $cacheKey, callable $realResolverInstantiator)
    {
        $this->cache                    = $cache;
        $this->cacheKey                 = $cacheKey;
        $this->realResolverInstantiator = $realResolverInstantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, ?RendererInterface $renderer = null)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }

        if ($this->map !== null) {
            return false;
        }

        $this->loadMap();

        return $this->resolve($name);
    }

    /**
     * Load the template map into memory
     */
    private function loadMap(): void
    {
        /** @var array<string, string>|false $map */
        $map = $this->cache->getItem($this->cacheKey);

        if (is_array($map)) {
            $this->map = $map;

            return;
        }

        $this->map = (new TemplateMapCompiler())->compileMap(($this->realResolverInstantiator)());

        $this->cache->setItem($this->cacheKey, $this->map);
    }
}
