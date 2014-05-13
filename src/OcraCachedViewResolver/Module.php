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

namespace OcraCachedViewResolver;

use OcraCachedViewResolver\Factory\CacheFactory;
use OcraCachedViewResolver\Factory\CompiledMapResolverDelegatorFactory;
use Zend\Cache\Storage\Adapter\Apc;
use Zend\Cache\Storage\Adapter\BlackHole;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * OcraCachedViewResolver module
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class Module implements ConfigProviderInterface
{
    /**
     * Name of the cache namespace where configs for this module are wrapped
     */
    const CONFIG = 'ocra_cached_view_resolver';

    /**
     * Name of the config key referencing the cache service to be used when storing the cached map
     */
    const CONFIG_CACHE_SERVICE = 'cache_service';

    /**
     * Name of the config key referencing the cache key to be used when storing the cached map
     */
    const CONFIG_CACHE_KEY = 'cached_template_map_key';

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return [
            self::CONFIG => [
                'cache' => [
                    'adapter' => Apc::class,
                ],
                self::CONFIG_CACHE_KEY     => 'cached_template_map',
                self::CONFIG_CACHE_SERVICE => 'OcraCachedViewResolver\\Cache\\DummyCache',
            ],
            'service_manager' => [
                'invokables' => [
                    'OcraCachedViewResolver\\Cache\\DummyCache' => BlackHole::class,
                ],
                'factories' => [
                    'OcraCachedViewResolver\\Cache\\ResolverCache' => CacheFactory::class,
                ],
                'delegators' => [
                    'ViewResolver' => [
                        CompiledMapResolverDelegatorFactory::class => CompiledMapResolverDelegatorFactory::class,
                    ],
                ],
            ],
        ];
    }
}
