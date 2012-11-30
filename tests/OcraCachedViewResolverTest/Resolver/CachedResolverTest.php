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

namespace OcraCachedViewResolverTest\Resolver;

use PHPUnit_Framework_TestCase;
use OcraCachedViewResolver\Resolver\CachedResolver;
use Zend\Cache\Storage\Adapter\Memory;

/**
 * Cached resolver tests
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CachedResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CachedResolver
     */
    protected $resolver;

    /**
     * @var \Zend\View\Resolver\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $originalResolver;

    /**
     * @var \Zend\Cache\Storage\StorageInterface|\PHPUnit_Framework_MockObject_MockObject;
     */
    protected $cache;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->originalResolver = $this->getMock('Zend\View\Resolver\ResolverInterface');
        $this->cache = $this->getMock('Zend\Cache\Storage\StorageInterface');
        $this->resolver = new CachedResolver($this->originalResolver, $this->cache);
    }

    public function testCallsOriginalResolver()
    {
        $renderer = $this->getMock('Zend\View\Renderer\RendererInterface');
        $this->originalResolver->expects($this->exactly(3))->method('resolve')->with('template-name', $renderer);

        $this->assertNull($this->resolver->resolve('template-name', $renderer));
        $this->assertNull($this->resolver->resolve('template-name', $renderer));
        $this->assertNull($this->resolver->resolve('template-name', $renderer));
    }

    public function testWillFillCacheEntryIfTemplateIsResolved()
    {
        $this
            ->originalResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('template-name')
            ->will($this->returnValue('resolved/path'));

        $this
            ->cache
            ->expects($this->once())
            ->method('setItem')
            ->with('template-name', 'resolved/path');

        $this->assertSame('resolved/path', $this->resolver->resolve('template-name'));
    }

    public function testWillNotFillCacheEntryIfTemplateIsNotResolved()
    {
        $this
            ->originalResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('template-name')
            ->will($this->returnValue(null));

        $this->cache->expects($this->never())->method('setItem');

        $this->assertNull($this->resolver->resolve('template-name'));
    }

    public function testWillNotResolveIfCacheEntryAvailable()
    {
        $this->originalResolver->expects($this->never())->method('resolve');

        $cache = new MockCache();
        $cache->item = 'resolved/path';

        $this->resolver = new CachedResolver($this->originalResolver, $cache);

        $this->assertSame('resolved/path', $this->resolver->resolve('template-name'));
        $this->assertSame('resolved/path', $this->resolver->resolve('template-name'));
        $this->assertSame('resolved/path', $this->resolver->resolve('template-name'));

        $this->assertSame(3, $cache->hits);
    }
}

class MockCache extends Memory
{
    /**
     * @var mixed
     */
    public $item;

    public $hits = 0;

    /**
     * Disabling parent constructor
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getItem($key, & $success = null, & $casToken = null)
    {
        $success = true;
        $this->hits += 1;

        return $this->item;
    }
}
