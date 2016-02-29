<?php
namespace Vanio\TypeParser\Tests;

use Doctrine\Common\Annotations\PhpParser;
use Vanio\TypeParser\Tests\Fixtures\Bar;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeParser;

class TypeParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var TypeParser */
    private $typeParser;

    /** @var Type[] */
    private $fooTypes;

    /** @var Type[] */
    private $barTypes;

    protected function setUp()
    {
        $this->typeParser = new TypeParser(new PhpParser);
        $this->fooTypes = [
            'arrayIterator' => new Type(\ArrayIterator::class),
            'typeParser' => new Type(TypeParser::class),
            'nullableType' => new Type(Type::class, true),
            'typeIterator' => new Type(\ArrayIterator::class, false, [Type::class]),
            'preciselyMergedTypeIterator' => new Type(\ArrayIterator::class, false, [Type::class]),
            'roughlyMergedTypeIterator' => new Type(\ArrayIterator::class),
            'nullableTypeIterator' => new Type(\ArrayIterator::class, true, [Type::class]),
            'genericArray' => new Type(Type::ARRAY, false, [Type::INTEGER, Type::class]),
            'mixed' => new Type(Type::MIXED),
            'extended' => new Type(Type::INTEGER),
            'string' => new Type(Type::STRING),
        ];
        $this->barTypes = ['extended' => new Type(Type::FLOAT)] + $this->fooTypes;
        unset($this->barTypes['string']);
    }

    function test_it_parses_property_types()
    {
        $this->assertEquals($this->fooTypes, $this->typeParser->parsePropertyTypes(Foo::class));
    }

    function test_it_parses_property_types_of_class_using_inheritance()
    {
        $this->assertEquals($this->barTypes, $this->typeParser->parsePropertyTypes(Bar::class));
    }
}
