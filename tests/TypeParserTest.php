<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Tests\Fixtures\Bar;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeContextFactory;
use Vanio\TypeParser\TypeParser;
use Vanio\TypeParser\TypeResolver;
use Vanio\TypeParser\UseStatementsParser;

class TypeParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Type[] */
    private static $fooTypes;

    /** @var Type[] */
    private static $barTypes;

    protected function setUp()
    {
        if (self::$fooTypes) {
            return;
        }

        $typeParser = new TypeParser(new TypeResolver, new TypeContextFactory(new UseStatementsParser));
        self::$fooTypes = $typeParser->parsePropertyTypes(Foo::class);
        self::$barTypes = $typeParser->parsePropertyTypes(Bar::class);
    }

    /**
     * @param string $property
     * @param string $type
     * @param Type[] $typeParameters
     * @param bool $nullable
     * @dataProvider fooTypes
     */
    function test_it_parses_property_types(string $property, string $type, array $typeParameters, bool $nullable)
    {
        $this->assertArrayHasKey($property, self::$fooTypes);
        $fooType = self::$fooTypes[$property];
        $this->assertSame($type, $fooType->type());
        $this->assertEquals($typeParameters, $fooType->typeParameters());
        $this->assertSame($nullable, $fooType->isNullable());

        $this->assertArrayHasKey($property, self::$barTypes);
        $barType = self::$barTypes[$property];
        $this->assertSame($type, $barType->type());
        $this->assertEquals($typeParameters, $barType->typeParameters());
        $this->assertSame($nullable, $barType->isNullable());
    }

    /**
     * @dataProvider fooTypes
     */
    function test_it_parses_property_types_of_child_class()
    {
        $this->assertSame(Type::INTEGER, self::$fooTypes['extended']->type());
        $this->assertSame(Type::FLOAT, self::$barTypes['extended']->type());
    }

    public function fooTypes(): array
    {
        return [
            ['arrayIterator', \ArrayIterator::class, [], false],
            ['typeParser', TypeParser::class, [], false],
            ['nullableType', Type::class, [], true],
            ['typeIterator', \ArrayIterator::class, [new SimpleType(Type::class)], false],
            ['preciselyMergedTypeIterator', \ArrayIterator::class, [new SimpleType(Type::class)], false],
            ['roughlyMergedTypeIterator', \ArrayIterator::class, [], false],
            ['nullableTypeIterator', \ArrayIterator::class, [new SimpleType(Type::class)], true],
            ['genericArray', Type::ARRAY, [new SimpleType(Type::INTEGER), new SimpleType(Type::class)], false],
            ['scalar', Type::SCALAR, [], false],
            ['mixed', Type::MIXED, [], false],
        ];
    }
}
