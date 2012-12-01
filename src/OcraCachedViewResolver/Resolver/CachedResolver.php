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

namespace OcraCachedViewResolver\Resolver;

use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * Cached resolver that uses a cache storage to speed up lookup of templates
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CachedResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var StorageInterface
     */
    protected $cache;

    /**
     * @param ResolverInterface $originalResolver
     * @param StorageInterface  $cache
     */
    public function __construct(ResolverInterface $originalResolver, StorageInterface $cache)
    {
        $this->resolver = $originalResolver;
        $this->cache    = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        $entry = $this->cache->getItem($name, $success);

        if ($success) {
            return $entry;
        }

        $entry = $this->resolver->resolve($name, $renderer);

        if ($entry) {
            $this->cache->setItem($name, $entry);
        }

        return $entry;
    }
}
