<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\CompoundType;
use Vanio\TypeParser\GenericType;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Type;

class SimpleTypeTest extends \PHPUnit_Framework_TestCase
{
    function test_type_can_be_obtained()
    {
        $this->assertSame(Type::STRING, (new SimpleType(Type::STRING))->type());
        $this->assertSame(Type::STRING, (new SimpleType('STRING'))->type());
        $this->assertSame(\stdClass::class, (new SimpleType(\stdClass::class))->type());
    }

    function test_it_can_be_scalar()
    {
        $this->assertTrue((new SimpleType(Type::SCALAR))->isScalar());
        $this->assertTrue((new SimpleType(Type::STRING))->isScalar());
        $this->assertTrue((new SimpleType(Type::INTEGER))->isScalar());
        $this->assertTrue((new SimpleType(Type::FLOAT))->isScalar());
        $this->assertTrue((new SimpleType(Type::BOOLEAN))->isScalar());
        $this->assertTrue((new SimpleType('STRING'))->isScalar());
    }

    function test_it_does_not_have_to_be_scalar()
    {
        $this->assertFalse((new SimpleType(Type::NULL))->isScalar());
        $this->assertFalse((new SimpleType(Type::OBJECT))->isScalar());
        $this->assertFalse((new SimpleType(Type::ARRAY))->isScalar());
        $this->assertFalse((new SimpleType(\stdClass::class))->isScalar());
    }

    function test_it_can_be_a_typed_object()
    {
        $this->assertTrue((new SimpleType(\stdClass::class))->isTypedObject());
        $this->assertTrue((new SimpleType(__CLASS__))->isTypedObject());
    }

    function test_it_does_not_have_to_be_a_typed_object()
    {
        $this->assertFalse((new SimpleType(Type::OBJECT))->isTypedObject());
        $this->assertFalse((new SimpleType(Type::STRING))->isTypedObject());
        $this->assertFalse((new SimpleType(Type::RESOURCE))->isTypedObject());
    }

    function test_it_can_be_a_collection()
    {
        $this->assertTrue((new SimpleType(Type::ARRAY))->isCollection());
        $this->assertTrue((new SimpleType(\Traversable::class))->isCollection());
        $this->assertTrue((new SimpleType(\ArrayObject::class))->isCollection());
    }

    function test_it_does_not_have_to_be_a_collection()
    {
        $this->assertFalse((new SimpleType(Type::STRING))->isCollection());
        $this->assertFalse((new SimpleType(\stdClass::class))->isCollection());
    }

    function test_it_can_be_nullable()
    {
        $this->assertTrue((new SimpleType(Type::NULL))->isNullable());
        $this->assertTrue((new SimpleType(Type::MIXED))->isNullable());
    }

    function test_it_does_not_have_to_be_nullable()
    {
        $this->assertFalse((new SimpleType(Type::STRING))->isNullable());
        $this->assertFalse((new SimpleType(\stdClass::class))->isNullable());
    }

    function test_it_cannot_be_generic()
    {
        $this->assertFalse((new SimpleType(Type::STRING))->isGeneric());
    }

    function test_it_cannot_be_compound()
    {
        $this->assertFalse((new SimpleType(Type::STRING))->isCompound());
    }

    function test_type_parameters_are_empty()
    {
        $this->assertSame([], (new SimpleType(Type::STRING))->typeParameters());
    }

    function test_comparing_to_another_type()
    {
        $this->assertTrue((new SimpleType(Type::STRING))->equals(new SimpleType(Type::STRING)));
        $this->assertTrue((new SimpleType(\stdClass::class))->equals(new SimpleType(\stdClass::class)));
        $this->assertFalse((new SimpleType(Type::INTEGER))->equals(new SimpleType(Type::FLOAT)));
        $this->assertFalse((new SimpleType(Type::INTEGER))->equals(Type::INTEGER));
    }

    function test_merging_with_same_type()
    {
        $simpleType = new SimpleType(Type::STRING);
        $this->assertTrue($simpleType->merge($simpleType)->equals($simpleType));
    }

    function test_merging_with_compound_type_is_not_supported()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Merging of compound types is not supported.');

        (new SimpleType(Type::STRING))->merge(new CompoundType(Type::STRING, Type::STRING));
    }

    function test_merging_with_null()
    {
        $simpleType = new SimpleType(Type::STRING);
        $this->assertTrue($simpleType->merge(new SimpleType(Type::NULL))->isNullable());

        $simpleType = new SimpleType(Type::MIXED);
        $this->assertTrue($simpleType->merge(new SimpleType(Type::NULL))->equals($simpleType));
    }

    function test_merging_with_scalar()
    {
        $simpleType = new SimpleType(Type::STRING);
        $this->assertTrue($simpleType->merge(new SimpleType(Type::INTEGER))->equals(new SimpleType(Type::SCALAR)));
    }

    function test_merging_traversable_with_typed_array()
    {
        $simpleType = new SimpleType(\ArrayIterator::class);
        $merged = $simpleType->merge(new GenericType(Type::ARRAY, [Type::STRING]));
        $this->assertSame(\ArrayIterator::class, $merged->type());
        $this->assertEquals([Type::STRING], $merged->typeParameters());
    }

    function test_merging_typed_object_with_parent_type()
    {
        $parentType = new SimpleType(\Traversable::class);
        $childType = new SimpleType(\ArrayIterator::class);
        $this->assertSame(\Traversable::class, $parentType->merge($childType)->type());
        $this->assertSame(\Traversable::class, $childType->merge($parentType)->type());
    }

    function test_merging_typed_object_with_generic_child_type()
    {
        $type = (new SimpleType(\Traversable::class))->merge(new GenericType(\ArrayIterator::class, [Type::STRING]));
        $this->assertSame(\Traversable::class, $type->type());
        $this->assertFalse($type->isGeneric());
    }

    function test_merging_typed_object_with_generic_parent_type()
    {
        $type = (new SimpleType(\ArrayIterator::class))->merge(new GenericType(\Traversable::class, [Type::STRING]));
        $this->assertSame(\Traversable::class, $type->type());
        $this->assertFalse($type->isGeneric());
    }

    function test_merging_an_object_with_a_different_object()
    {
        $type = (new SimpleType(\stdClass::class))->merge(new SimpleType(__CLASS__));
        $this->assertSame(Type::OBJECT, $type->type());

        $type = (new SimpleType(\stdClass::class))->merge(new SimpleType(Type::OBJECT));
        $this->assertSame(Type::OBJECT, $type->type());
        $this->assertFalse($type->isGeneric());
    }

    function test_merging_with_a_different_type()
    {
        $type = (new SimpleType(Type::OBJECT))->merge(new SimpleType(Type::STRING));
        $this->assertSame(Type::MIXED, $type->type());

        $type = (new SimpleType(\stdClass::class))->merge(new SimpleType(Type::RESOURCE));
        $this->assertSame(Type::MIXED, $type->type());
    }

    function test_it_can_be_casted_to_string()
    {
        $this->assertSame(Type::STRING, (string) new SimpleType(Type::STRING));
        $this->assertSame(Type::INTEGER, (string) new SimpleType(Type::INTEGER));
        $this->assertSame(\stdClass::class, (string) new SimpleType(\stdClass::class));
    }
}
