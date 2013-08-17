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

use Zend\View\Resolver\ResolverInterface;
use Zend\View\Resolver\TemplatePathStack;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Resolver\AggregateResolver;

use Zend\Stdlib\ArrayUtils;

use Zend\View\Exception\InvalidArgumentException;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;

/**
 * Template map generator that can build template map arrays from either
 * an {@see \Zend\View\Resolver\TemplateMapResolver}, a
 * {@see \Zend\View\Resolver\TemplatePathStack} or a
 * {@see \Zend\View\Resolver\AggregateResolver}
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
     */
    public function compileMap(ResolverInterface $resolver)
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

        return array();
    }

    /**
     * @param AggregateResolver $resolver
     *
     * @return array
     */
    protected function compileFromAggregateResolver(AggregateResolver $resolver)
    {
        $map = array();

        /* @var $queuedResolver ResolverInterface */
        foreach ($resolver->getIterator()->toArray() as $queuedResolver) {
            $map = ArrayUtils::merge($map, $this->compileMap($queuedResolver));
        }

        return $map;
    }

    /**
     * @param TemplatePathStack $resolver
     *
     * @return array
     */
    protected function compileFromTemplatePathStack(TemplatePathStack $resolver)
    {
        $map = array();

        foreach ($resolver->getPaths()->toArray() as $path) {
            $path     = realpath($path);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            /* @var $file \SplFileInfo */
            foreach ($iterator as $file) {
                $filePath      = $file->getRealPath();
                $fileName      = pathinfo($filePath, PATHINFO_FILENAME);
                $relativePath  = trim(str_replace($path, '', $file->getPath()), '/\\');
                $templateName  = str_replace('\\', '/', trim($relativePath . '/' . $fileName, '/'));

                if ($fileName && ($resolvedPath = $resolver->resolve($templateName))) {
                    $map[$templateName] = realpath($resolvedPath);
                }
            }
        }

        return $map;
    }

    /**
     * @param TemplateMapResolver $resolver
     *
     * @return array
     */
    protected function compileFromTemplateMapResolver(TemplateMapResolver $resolver)
    {
        return $resolver->getMap();
    }
}
