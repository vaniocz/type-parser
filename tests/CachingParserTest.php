<?php
namespace Vanio\TypeParser\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Vanio\TypeParser\CachingParser;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeParser;

class CachingParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Type[] */
    private $types;

    /** @var ArrayCache */
    private $cache;

    /** @var TypeParser|\PHPUnit_Framework_MockObject_MockObject */
    private $typeParser;

    protected function setUp()
    {
        $this->types = [new SimpleType(Type::STRING)];
        $this->typeParser = $this->getMockWithoutInvokingTheOriginalConstructor(TypeParser::class);
        $this->typeParser->expects($this->once())
            ->method('parsePropertyTypes')
            ->with(Foo::class)
            ->willReturn($this->types);
        $this->cache = new ArrayCache;
    }

    function test_parsing_property_types()
    {
        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));
        $this->assertNotSame(0, (int) $namespace = $this->cache->getNamespace());

        $cachingParser = new CachingParser($this->typeParser, $this->cache);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));
        $this->assertSame($namespace, $this->cache->getNamespace());
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $this->assertSame(1, $this->cache->getStats()['hits']);
    }

    function test_parsing_property_types_without_cache_invalidation()
    {
        $cachingParser = new CachingParser($this->typeParser, $this->cache, false);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));
        $this->assertEmpty($this->cache->getNamespace());

        $cachingParser = new CachingParser($this->typeParser, $this->cache, false);
        $this->assertEquals($this->types, $cachingParser->parsePropertyTypes(Foo::class));

        $this->assertSame(1, $this->cache->getStats()['hits']);
    }
}
