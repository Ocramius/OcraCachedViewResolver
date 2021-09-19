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

namespace OcraCachedViewResolver\View\Resolver\Exception;

use InvalidArgumentException;
use Laminas\View\Resolver\ResolverInterface;

/**
 * Exception for invalid instantiators
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class InvalidResolverInstantiatorException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $instantiator
     *
     * @return self
     */
    public static function fromInvalidInstantiator($instantiator) : self
    {
        return new self(sprintf(
            'Invalid instantiator given, expected `callable`, `%s` given.',
            is_object($instantiator) ? get_class($instantiator) : gettype($instantiator)
        ));
    }

    /**
     * @param mixed $resolver
     *
     * @return self
     */
    public static function fromInvalidResolver($resolver) : self
    {
        return new self(sprintf(
            'Invalid resolver found, expected `' . ResolverInterface::class . '`, `%s` given.',
            is_object($resolver) ? get_class($resolver) : gettype($resolver)
        ));
    }
}
