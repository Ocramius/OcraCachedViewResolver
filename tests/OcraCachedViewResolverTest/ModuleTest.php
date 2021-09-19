<?php

declare(strict_types=1);

namespace OcraCachedViewResolverTest;

use OcraCachedViewResolver\Module;
use PHPUnit\Framework\TestCase;

use function serialize;
use function unserialize;

/**
 * Tests for {@see \OcraCachedViewResolver\Module}
 *
 * @covers \OcraCachedViewResolver\Module
 */
class ModuleTest extends TestCase
{
    public function testConfigIsAnArray(): void
    {
        self::assertIsArray((new Module())->getConfig());
    }

    public function testConfigIsSerializable(): void
    {
        $module = new Module();

        self::assertSame($module->getConfig(), unserialize(serialize($module->getConfig())));
    }
}
