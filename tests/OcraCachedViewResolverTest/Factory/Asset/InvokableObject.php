<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\Factory\Asset;

use Laminas\View\Resolver\ResolverInterface;

/** Allows us to stub an invokable object */
interface InvokableObject
{
    public function __invoke(): ResolverInterface;
}
