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

namespace OcraCachedViewResolverTest\View\Resolver;

use Interop\Container\ContainerInterface;
use OcraCachedViewResolver\Factory\CacheFactory;
use OcraCachedViewResolver\Module;
use PHPUnit\Framework\TestCase;
use Zend\Cache\Storage\Adapter\Memory;

/**
 * Tests for {@see \OcraCachedViewResolver\Factory\CacheFactory}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * @covers \OcraCachedViewResolver\Factory\CacheFactory
 */
class CacheFactoryTest extends TestCase
{
    public function testCreateService()
    {
        /* @var $locator ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator = $this->createMock(ContainerInterface::class);

        $locator->expects(self::any())->method('get')->with('Config')->will(self::returnValue([
            Module::CONFIG => [
                Module::CONFIG_CACHE_DEFINITION => [
                    'adapter' => Memory::class,
                ],
            ],
        ]));

        self::assertInstanceOf(Memory::class, (new CacheFactory())->__invoke($locator));
    }
}
