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

namespace OcraCachedViewResolver\View\Resolver;

use OcraCachedViewResolver\Compiler\TemplateMapCompiler;
use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

/**
 * OcraCachedViewResolver module
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class CachingMapResolver implements ResolverInterface
{
    /**
     * @var callable
     */
    private $realResolverInstantiator;

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var null|array
     */
    private $map;

    /**
     * @param StorageInterface $cache
     * @param string           $cacheKey
     * @param callable         $realResolverInstantiator
     */
    public function __construct(StorageInterface $cache, $cacheKey, callable $realResolverInstantiator)
    {
        $this->cache                    = $cache;
        $this->cacheKey                 = (string) $cacheKey;
        $this->realResolverInstantiator = $realResolverInstantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name];
        }

        if (null !== $this->map) {
            return false;
        }

        $this->loadMap();

        return $this->resolve($name);
    }

    /**
     * Load the template map into memory
     */
    private function loadMap()
    {
        $this->map = $this->cache->getItem($this->cacheKey);

        if (is_array($this->map)) {
            return;
        }

        $realResolverInstantiator = $this->realResolverInstantiator;
        $realResolver             = $realResolverInstantiator();

        if (! $realResolver instanceof ResolverInterface) {
            throw InvalidResolverInstantiatorException::fromInvalidResolver($realResolver);
        }

        $this->map = (new TemplateMapCompiler())->compileMap($realResolver);

        $this->cache->setItem($this->cacheKey, $this->map);
    }
}
