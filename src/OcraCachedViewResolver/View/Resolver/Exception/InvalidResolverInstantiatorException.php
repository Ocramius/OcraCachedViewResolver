<?php

declare(strict_types=1);

namespace OcraCachedViewResolver\View\Resolver\Exception;

use InvalidArgumentException;
use Laminas\View\Resolver\ResolverInterface;

use function gettype;
use function is_object;
use function sprintf;

/**
 * Exception for invalid instantiators
 */
final class InvalidResolverInstantiatorException extends InvalidArgumentException implements ExceptionInterface
{
    public static function fromInvalidResolver(mixed $resolver): self
    {
        return new self(sprintf(
            'Invalid resolver found, expected `' . ResolverInterface::class . '`, `%s` given.',
            is_object($resolver) ? $resolver::class : gettype($resolver)
        ));
    }
}
