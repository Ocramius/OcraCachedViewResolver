<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest\View\Resolver;

use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\Adapter\Memory;
use OcraCachedViewResolver\Factory\CacheFactory;
use OcraCachedViewResolver\Module;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \OcraCachedViewResolver\Factory\CacheFactory}
 *
 * @group Coverage
 * @covers \OcraCachedViewResolver\Factory\CacheFactory
 */
class CacheFactoryTest extends TestCase
{
    public function testCreateService(): void
    {
        $locator = $this->createMock(ContainerInterface::class);

        $locator->method('get')->with('Config')->will(self::returnValue([
            Module::CONFIG => [
                Module::CONFIG_CACHE_DEFINITION => [
                    'adapter' => Memory::class,
                ],
            ],
        ]));

        self::assertInstanceOf(Memory::class, (new CacheFactory())->__invoke($locator));
    }
}
