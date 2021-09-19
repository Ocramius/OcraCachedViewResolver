<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\View\Resolver;

use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

/**
 * Lazy resolver, only instantiates the actual resolver if it is needed
 */
final class LazyResolver implements ResolverInterface
{
    /** @var callable(): ResolverInterface $makeResolver */
    private $makeResolver;

    private ?ResolverInterface $resolver = null;

    /** @param callable(): ResolverInterface $makeResolver */
    public function __construct(callable $makeResolver)
    {
        $this->makeResolver = $makeResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, ?RendererInterface $renderer = null)
    {
        if (! $this->resolver) {
            $this->resolver = ($this->makeResolver)();
        }

        return $this->resolver->resolve($name, $renderer);
    }
}
