<?php
namespace Vanio\TypeParser\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\TypeParser\CompoundType;
use Vanio\TypeParser\GenericType;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Type;

class CompoundTypeTest extends TestCase
{
    /** @var SimpleType[] */
    private $types;

    /** @var CompoundType */
    private $type;

    protected function setUp()
    {
        $this->types = [new SimpleType(Type::INTEGER), new SimpleType(Type::STRING)];
        $this->type = new CompoundType(...$this->types);
    }

    function test_compound_type_cannot_be_part_of_another_compound_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Compound type cannot be part of another compound type.');

        $compoundType = new CompoundType(Type::STRING);
        new CompoundType($compoundType, $compoundType);
    }

    function test_it_is_iterable()
    {
        $this->assertSame($this->types, iterator_to_array($this->type));
    }

    function test_type_can_be_obtained()
    {
        $this->assertSame(Type::SCALAR, $this->type->type());
        $this->assertSame(Type::NULL, (new CompoundType(Type::NULL, Type::NULL))->type());
        $this->assertSame(Type::STRING, (new CompoundType(Type::NULL, Type::STRING))->type());
    }

    function test_it_can_be_scalar()
    {
        $this->assertTrue($this->type->isScalar());
    }

    function test_it_does_not_have_to_be_scalar()
    {
        $this->assertFalse((new CompoundType(Type::OBJECT, Type::STRING))->isScalar());
    }

    function test_it_can_be_a_typed_object()
    {
        $this->assertTrue((new CompoundType(__CLASS__, __CLASS__))->isTypedObject());
    }

    function test_it_does_not_have_to_be_a_typed_object()
    {
        $this->assertFalse($this->type->isTypedObject());
    }

    function test_it_can_be_a_collection()
    {
        $this->assertTrue((new CompoundType(Type::ARRAY, Type::ARRAY))->isCollection());
    }

    function test_it_does_not_have_to_be_a_collection()
    {
        $this->assertFalse($this->type->isCollection());
    }

    function test_it_can_be_nullable()
    {
        $this->assertTrue((new CompoundType(Type::STRING, Type::NULL))->isNullable());
        $this->assertTrue((new CompoundType(Type::NULL, Type::STRING))->isNullable());
    }

    function test_it_does_not_have_to_be_nullable()
    {
        $this->assertFalse($this->type->isNullable());
    }

    function test_it_can_be_generic()
    {
        $genericType = new GenericType(Type::ARRAY, [Type::STRING]);
        $this->assertTrue((new CompoundType($genericType, $genericType))->isGeneric());
    }

    function test_it_does_not_have_to_be_generic()
    {
        $this->assertFalse($this->type->isGeneric());
    }

    function test_it_is_compound()
    {
        $this->assertTrue($this->type->isCompound());
    }

    function test_primary_type_can_be_obtained()
    {
        $this->assertSame(Type::INTEGER, $this->type->primaryType()->type());
        $this->assertSame(Type::STRING, (new CompoundType(Type::NULL, Type::STRING))->primaryType()->type());
    }

    function test_type_parameters_can_be_obtained()
    {
        $genericType = new GenericType(Type::ARRAY, [Type::STRING]);
        $type = new CompoundType($genericType, $genericType);
        $this->assertEquals([new SimpleType(Type::STRING)], $type->typeParameters());

        $this->assertSame([], $this->type->typeParameters());
    }

    function test_comparing_to_same_type()
    {
        $this->assertTrue($this->type->equals($this->type));
        $this->assertTrue($this->type->equals(new CompoundType(Type::STRING, Type::INTEGER)));
    }

    function test_comparing_to_a_different_type()
    {
        $this->assertFalse($this->type->equals(new CompoundType(Type::STRING, Type::STRING)));
        $this->assertFalse($this->type->equals(Type::ARRAY));
    }

    function test_merging_of_compound_type_is_not_possible()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Merging of compound types is not supported.');

        $this->type->merge(new SimpleType(Type::SCALAR));
    }

    function test_it_can_be_casted_to_string()
    {
        $this->assertSame('int|string', (string) $this->type);
        $this->assertSame('string|null', (string) new CompoundType(Type::STRING, Type::NULL));
    }
}
