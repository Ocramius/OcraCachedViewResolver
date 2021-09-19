<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace OcraCachedViewResolver\Compiler;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\ResolverInterface;
use Laminas\View\Resolver\TemplateMapResolver;
use Laminas\View\Resolver\TemplatePathStack;

/**
 * Template map generator that can build template map arrays from either
 * an {@see \Laminas\View\Resolver\TemplateMapResolver}, a
 * {@see \Laminas\View\Resolver\TemplatePathStack} or a
 * {@see \Laminas\View\Resolver\AggregateResolver}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class TemplateMapCompiler
{
    /**
     * Generates a list of all existing templates in the given resolver,
     * with their names being keys, and absolute paths being values
     *
     * @param ResolverInterface $resolver
     *
     * @return array
     *
     * @throws \Laminas\View\Exception\DomainException
     */
    public function compileMap(ResolverInterface $resolver) : array
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

    protected function compileFromAggregateResolver(AggregateResolver $resolver) : array
    {
        $map = [];

        /* @var $queuedResolver ResolverInterface */
        foreach ($resolver->getIterator() as $queuedResolver) {
            $map = ArrayUtils::merge($this->compileMap($queuedResolver), $map);
        }

        return $map;
    }

    /**
     * @param TemplatePathStack $resolver
     *
     * @return array
     *
     * @throws \Laminas\View\Exception\DomainException
     */
    protected function compileFromTemplatePathStack(TemplatePathStack $resolver) : array
    {
        $map = [];

        foreach ($resolver->getPaths()->toArray() as $path) {
            $path     = realpath($path);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $this->addResolvedPath($file, $map, $path, $resolver);
            }
        }

        return $map;
    }

    protected function compileFromTemplateMapResolver(TemplateMapResolver $resolver) : array
    {
        return $resolver->getMap();
    }

    /**
     * Add the given file to the map if it corresponds to a resolved view
     *
     * @param SplFileInfo       $file
     * @param array             $map
     * @param string            $basePath
     * @param TemplatePathStack $resolver
     *
     * @return void
     *
     * @throws \Laminas\View\Exception\DomainException
     */
    private function addResolvedPath(SplFileInfo $file, array & $map, $basePath, TemplatePathStack $resolver)
    {
        $filePath      = $file->getRealPath();
        $fileName      = pathinfo($filePath, PATHINFO_FILENAME);
        $relativePath  = trim(str_replace($basePath, '', $file->getPath()), '/\\');
        $templateName  = str_replace('\\', '/', trim($relativePath . '/' . $fileName, '/'));

        if ($fileName && ($resolvedPath = $resolver->resolve($templateName))) {
            $map[$templateName] = realpath($resolvedPath);
        }
    }
}
