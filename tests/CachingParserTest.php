<?php
namespace Vanio\TypeParser\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Vanio\TypeParser\CachingParser;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Tests\Fixtures\Bar;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeParser;

class CachingParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Type[] */
    private $types;

    /** @var ArrayCache|\PHPUnit_Framework_MockObject_MockObject */
    private $cache;

    /** @var TypeParser|\PHPUnit_Framework_MockObject_MockObject */
    private $typeParser;

    protected function setUp()
    {
        $this->types = [new SimpleType(Type::STRING)];
        $this->typeParser = $this->createMock(TypeParser::class);
        $this->cache = $this->getMockBuilder(ArrayCache::class)->enableProxyingToOriginalMethods()->getMock();
    }

    function test_parsing_property_types()
    {
        $this->typeParser->expects($this->once())
            ->method('parsePropertyTypes')
            ->with(Foo::class)
            ->willReturn($this->types);
        $cacheIdPattern = sprintf(
            '~%s\[.*\Foo\.php\]\[\d+]\[%s]$~',
            preg_quote(CachingParser::class),
            preg_quote(Foo::class)
        );
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->matchesRegularExpression($cacheIdPattern));

        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $this->assertSame(1, $this->cache->getStats()['hits']);
    }

    function test_parsing_child_class_property_types()
    {
        $this->typeParser->expects($this->once())
            ->method('parsePropertyTypes')
            ->with(Bar::class)
            ->willReturn($this->types);
        $cacheIdPattern = sprintf(
            '~%s\[.*\Bar\.php\]\[\d+,\d+\]\[%s]$~',
            preg_quote(CachingParser::class),
            preg_quote(Bar::class)
        );
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with($this->matchesRegularExpression($cacheIdPattern));

        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Bar::class));

        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Bar::class));
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Bar::class));

        $this->assertSame(1, $this->cache->getStats()['hits']);
    }

    function test_parsing_property_types_without_cache_invalidation()
    {
        $this->typeParser->expects($this->once())
            ->method('parsePropertyTypes')
            ->with(Foo::class)
            ->willReturn($this->types);
        $this->cache->expects($this->any())
            ->method('fetch')
            ->with(sprintf('%s[%s]', CachingParser::class, Foo::class));

        $cachingParser = new CachingParser($this->typeParser, $this->cache, false);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $cachingParser = new CachingParser($this->typeParser, $this->cache, false);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $this->assertSame(1, $this->cache->getStats()['hits']);
    }
}
