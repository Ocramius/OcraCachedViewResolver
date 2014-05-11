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

namespace OcraCachedViewResolver\Factory;

use OcraCachedViewResolver\Compiler\TemplateMapCompiler;

use OcraCachedViewResolver\View\Resolver\LazyResolver;

use Zend\Cache\Storage\StorageInterface;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Factory responsible of building a {@see \Zend\View\Resolver\TemplateMapResolver}
 * from cached template definitions
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CompiledMapResolverDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return AggregateResolver
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $config   = $serviceLocator->get('Config');
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache    = $serviceLocator->get('OcraCachedViewResolver\\Cache\\ResolverCache');
        $cacheKey = $config['ocra_cached_view_resolver']['cached_template_map_key'];

        return $this->loadFromCache($cache, new AggregateResolver(), $cacheKey, $callback);
    }

    /**
     * Load from cache or fill the cache with the resolver's data, and provide an aggregate resolver
     * with the filled cached map resolver and a fallback resolver
     *
     * @param StorageInterface  $cache
     * @param AggregateResolver $resolver
     * @param string            $cacheKey
     * @param callable          $resolverCallback
     *
     * @return AggregateResolver
     */
    private function loadFromCache(StorageInterface $cache, AggregateResolver $resolver, $cacheKey, $resolverCallback)
    {
        $map = $cache->getItem($cacheKey);

        if (! is_array($map)) {
            /* @var $originalResolver \Zend\View\Resolver\ResolverInterface */
            $originalResolver = $resolverCallback();
            $compiler         = new TemplateMapCompiler();
            $map              = $compiler->compileMap($originalResolver);

            $cache->setItem($cacheKey, $map);
            $resolver->attach($originalResolver, 50);
        } else {
            $resolver->attach(new LazyResolver($resolverCallback), 50);
        }

        $resolver->attach(new TemplateMapResolver($map), 100);

        return $resolver;
    }
}
