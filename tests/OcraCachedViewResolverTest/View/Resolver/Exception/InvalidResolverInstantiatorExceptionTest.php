<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver\Exception;

use OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException}
 *
 * @covers \OcraCachedViewResolver\View\Resolver\Exception\InvalidResolverInstantiatorException
 * @covers \OcraCachedViewResolver\View\Resolver\Exception\ExceptionInterface
 */
class InvalidResolverInstantiatorExceptionTest extends TestCase
{
    public function testInstanceOfBaseExceptionInterface(): void
    {
        self::assertInstanceOf(
            InvalidResolverInstantiatorException::class,
            new InvalidResolverInstantiatorException()
        );
    }

    public function testFromInvalidNullResolver(): void
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidResolver(null);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid resolver found, expected `Laminas\View\Resolver\ResolverInterface`, `NULL` given.',
            $exception->getMessage()
        );
    }

    public function testFromInvalidObjectResolver(): void
    {
        $exception = InvalidResolverInstantiatorException::fromInvalidResolver($this);

        self::assertInstanceOf(InvalidResolverInstantiatorException::class, $exception);
        self::assertSame(
            'Invalid resolver found, expected `Laminas\View\Resolver\ResolverInterface`, `' . self::class . '` given.',
            $exception->getMessage()
        );
    }
}
