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

namespace OcraCachedViewResolverTest\View\Resolver\Exception;

use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException
 * @covers \OcraCachedViewResolver\View\Resolver\Exception\ExceptionInterface
 */
class InvalidResolverInstantiatorExceptionTest extends TestCase
{
    public function testInstanceOfBaseExceptionInterface()
    {
        self::assertInstanceOf(
            InvalidResolverInstantiatorException::class,
            new InvalidResolverInstantiatorException()
        );
    }

    public function testFromInvalidNullInstantiator()
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidInstantiator(null);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid instantiator given, expected `callable`, `NULL` given.',
            $exception->getMessage()
        );
    }

    public function testFromInvalidObjectInstantiator()
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidInstantiator($this);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid instantiator given, expected `callable`, `' . __CLASS__ . '` given.',
            $exception->getMessage()
        );
    }

    public function testFromInvalidNullResolver()
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidResolver(null);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid resolver found, expected `Zend\View\Resolver\ResolverInterface`, `NULL` given.',
            $exception->getMessage()
        );
    }

    public function testFromInvalidObjectResolver()
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidResolver($this);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid resolver found, expected `Zend\View\Resolver\ResolverInterface`, `' . __CLASS__ . '` given.',
            $exception->getMessage()
        );
    }
}
