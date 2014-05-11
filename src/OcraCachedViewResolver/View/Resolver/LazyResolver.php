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

use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

/**
 * OcraCachedViewResolver module
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyResolver implements ResolverInterface
{
    /**
     * @var callable
     */
    private $resolverInstantiator;

    /**
     * @var ResolverInterface|null
     */
    private $resolver;

    public function __construct($resolverInstantiator)
    {
        if (! is_callable($resolverInstantiator)) {
            throw InvalidResolverInstantiatorException::fromInvalidInstantiator($resolverInstantiator);
        }

        $this->resolverInstantiator = $resolverInstantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        if (! $this->resolver) {
            $resolverInstantiator = $this->resolverInstantiator;
            $resolver             =  $resolverInstantiator();

            if (! $resolver instanceof ResolverInterface) {
                throw InvalidResolverInstantiatorException::fromInvalidResolver($resolver);
            }

            $this->resolver = $resolver;
        }

        return $this->resolver->resolve($name, $renderer);
    }
}