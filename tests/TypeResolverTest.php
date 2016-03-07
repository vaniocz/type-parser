<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\CompoundType;
use Vanio\TypeParser\GenericType;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeContext;
use Vanio\TypeParser\TypeResolver;

class TypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var TypeResolver */
    private $typeResolver;

    /** @var TypeContext */
    private $typeContext;

    protected function setUp()
    {
        $this->typeResolver = new TypeResolver;
        $this->typeContext = TypeContext::fromClass(__CLASS__, \stdClass::class, ['bar' => 'Foo\Bar']);
    }

    function test_resolving_keyword()
    {
        $this->assertSame(Type::STRING, $this->typeResolver->resolveType('string', $this->typeContext)->type());
        $this->assertSame(Type::INTEGER, $this->typeResolver->resolveType('int', $this->typeContext)->type());
        $this->assertSame(Type::ARRAY, $this->typeResolver->resolveType('array', $this->typeContext)->type());
    }

    function test_resolving_pseudo_object_keyword()
    {
        $this->assertSame(__CLASS__, $this->typeResolver->resolveType('self', $this->typeContext)->type());
        $this->assertSame(\stdClass::class, (string) $this->typeResolver->resolveType('static', $this->typeContext));
        $this->assertSame(\stdClass::class, (string) $this->typeResolver->resolveType('$this', $this->typeContext));
    }

    function test_resolving_class()
    {
        $type = $this->typeResolver->resolveType('Foo', $this->typeContext);
        $this->assertSame('Vanio\TypeParser\Tests\Foo', $type->type());

        $this->assertSame('Foo\Bar', $this->typeResolver->resolveType('Bar', $this->typeContext)->type());
    }

    function test_resolving_typed_array()
    {
        $type = $this->typeResolver->resolveType('string[]', $this->typeContext);
        $this->assertSame(Type::ARRAY, $type->type());
        $this->assertEquals('string[]', (string) $type);

        $type = $this->typeResolver->resolveType('(int|string)[]', $this->typeContext);
        $this->assertSame(Type::ARRAY, $type->type());
        $this->assertEquals('(int|string)[]', (string) $type);
        $this->assertEquals([new CompoundType(Type::INTEGER, Type::STRING)], $type->typeParameters());
    }

    function test_resolving_generic_type()
    {
        $type = $this->typeResolver->resolveType('array<string>', $this->typeContext);
        $this->assertSame(Type::ARRAY, $type->type());
        $this->assertEquals([new SimpleType(Type::STRING)], $type->typeParameters());

        $type = $this->typeResolver->resolveType('\ArrayIterator<string>', $this->typeContext);
        $this->assertSame(\ArrayIterator::class, $type->type());
        $this->assertEquals([new SimpleType(Type::STRING)], $type->typeParameters());

        $type = $this->typeResolver->resolveType('\ArrayIterator<int, string>', $this->typeContext);
        $this->assertSame(\ArrayIterator::class, $type->type());
        $this->assertEquals([new SimpleType(Type::INTEGER), new SimpleType(Type::STRING)], $type->typeParameters());

        $type = $this->typeResolver->resolveType('\ArrayIterator<\ArrayIterator<string>>', $this->typeContext);
        $typeParameters = [new GenericType(\ArrayIterator::class, [new SimpleType(Type::STRING)])];
        $this->assertSame(\ArrayIterator::class, $type->type());
        $this->assertEquals($typeParameters, $type->typeParameters());
    }

    function test_resolving_compound()
    {
        $this->assertSame('int|string', (string) $this->typeResolver->resolveType('int|string', $this->typeContext));
    }
}
