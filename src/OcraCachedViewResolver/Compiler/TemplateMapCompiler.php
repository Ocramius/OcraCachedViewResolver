<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\Compiler;

use FilesystemIterator;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Exception\DomainException;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;
use function is_string;
use function pathinfo;
use function realpath;
use function str_replace;
use function trim;

use const PATHINFO_FILENAME;

/**
 * Template map generator that can build template map arrays from either
 * an {@see \Laminas\View\Resolver\TemplateMapResolver}, a
 * {@see \Laminas\View\Resolver\TemplatePathStack} or a
 * {@see \Laminas\View\Resolver\AggregateResolver}
 */
class TemplateMapCompiler
{
    /**
     * Generates a list of all existing templates in the given resolver,
     * with their names being keys, and absolute paths being values
     *
     * @return array<string, string>
     *
     * @throws DomainException
     */
    public function compileMap(ResolverInterface $resolver): array
    {
        if ($resolver instanceof AggregateResolver) {
            return $this->compileFromAggregateResolver($resolver);
        }

        if ($resolver instanceof TemplatePathStack) {
            return $this->compileFromTemplatePathStack($resolver);
        }

        if ($resolver instanceof TemplateMapResolver) {
            return $this->compileFromTemplateMapResolver($resolver);
        }

        return [];
    }

    /** @psalm-return array<string, string> */
    protected function compileFromAggregateResolver(AggregateResolver $resolver): array
    {
        $map = [];

        foreach ($resolver->getIterator() as $queuedResolver) {
            assert($queuedResolver instanceof ResolverInterface);
            /** @psalm-var array<string, string> $map */
            $map = ArrayUtils::merge($this->compileMap($queuedResolver), $map);
        }

        return $map;
    }

    /**
     * @return array<string, string>
     *
     * @throws DomainException
     */
    protected function compileFromTemplatePathStack(TemplatePathStack $resolver): array
    {
        $map = [];

        foreach ($resolver->getPaths()->toArray() as $path) {
            assert(is_string($path));
            $path = realpath($path);
            /** @var iterable<SplFileInfo> $iterator */
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $map = $this->addResolvedPath($file, $map, $path, $resolver);
            }
        }

        return $map;
    }

    /**
     * @psalm-return array<string, string>
     *
     * @psalm-suppress MixedReturnTypeCoercion the {@see TemplateMapResolver} does not have refined type declarations
     */
    protected function compileFromTemplateMapResolver(TemplateMapResolver $resolver): array
    {
        return $resolver->getMap();
    }

    /**
     * Add the given file to the map if it corresponds to a resolved view
     *
     * @param array<string, string> $map
     *
     * @return array<string, string>
     *
     * @throws DomainException
     */
    private function addResolvedPath(SplFileInfo $file, array $map, string $basePath, TemplatePathStack $resolver): array
    {
        $filePath     = $file->getRealPath();
        $fileName     = pathinfo($filePath, PATHINFO_FILENAME);
        $relativePath = trim(str_replace($basePath, '', $file->getPath()), '/\\');
        $templateName = str_replace('\\', '/', trim($relativePath . '/' . $fileName, '/'));

        if (! $fileName) {
            return $map;
        }

        $resolvedPath = $resolver->resolve($templateName);

        if (! $resolvedPath) {
            return $map;
        }

        $map[$templateName] = realpath($resolvedPath);

        return $map;
    }
}
