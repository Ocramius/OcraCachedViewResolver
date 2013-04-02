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

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * OcraCachedViewResolver module
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Module implements ConfigProviderInterface, InitProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        /* @var $moduleManager \Zend\ModuleManager\ModuleManager */
        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $moduleManager->getEvent()->getParam('ServiceManager');

        // We need to override services defined in the service listener
        $serviceManager->setAllowOverride(true);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return array(
            'ocra_cached_view_resolver' => array(
                'cache' => array(
                    'adapter' => 'apc',
                ),
                'cached_template_map_key' => 'cached_template_map',
            ),
            'service_manager' => array(
                'factories' => array(
                    'ViewResolver' => 'OcraCachedViewResolver\\Factory\\CompiledMapResolverFactory',
                    'OcraCachedViewResolver\\Resolver\\OriginalResolver' => 'Zend\\Mvc\\Service\\ViewResolverFactory',
                    'OcraCachedViewResolver\\Cache\\ResolverCache' => 'OcraCachedViewResolver\\Factory\\CacheFactory',
                ),
                'aliases' => array(
                    'Zend\\View\\Resolver\\AggregateResolver' => 'OcraCachedViewResolver\\Resolver\\OriginalResolver',
                    'OcraCachedViewResolver\\Resolver\\CompiledMapResolver' => 'ViewResolver',
                ),
            ),
        );
    }
}
