<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\CompoundType;
use Vanio\TypeParser\GenericType;
use Vanio\TypeParser\SimpleType;
use Vanio\TypeParser\Type;

class GenericTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var SimpleType|\PHPUnit_Framework_MockObject_MockObject */
    private $simpleTypeMock;

    /** @var GenericType */
    private $genericType;

    /** @var GenericType */
    private $typedArray;

    protected function setUp()
    {
        $this->simpleTypeMock = $this->getMockBuilder(SimpleType::class)
            ->setConstructorArgs([\ArrayIterator::class])
            ->enableProxyingToOriginalMethods()
            ->getMock();
        $this->genericType = new GenericType($this->simpleTypeMock, [Type::STRING]);
        $this->typedArray = new GenericType(Type::ARRAY, [Type::INTEGER, Type::STRING]);
    }

    function test_only_typed_objects_or_arrays_can_be_generic()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only typed objects or arrays can be generic.');

        new GenericType(Type::STRING, [Type::STRING]);
    }

    function test_generic_types_need_to_have_at_least_one_type_parameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Generic type needs to have at least one type parameter.');

        new GenericType(Type::ARRAY, []);
    }

    function test_type_can_be_obtained()
    {
        $this->simpleTypeMock->expects($this->atLeastOnce())->method('type');

        $this->assertSame(\ArrayIterator::class, $this->genericType->type());
    }

    function test_it_cannot_be_scalar()
    {
        $this->assertFalse($this->genericType->isScalar());
    }

    function test_it_can_be_a_typed_object()
    {
        $this->simpleTypeMock->expects($this->atLeastOnce())->method('isTypedObject');

        $this->assertTrue($this->genericType->isTypedObject());
    }

    function test_it_does_not_have_to_be_a_typed_object()
    {
        $this->assertFalse($this->typedArray->isTypedObject());
    }

    function test_it_can_be_a_collection()
    {
        $this->simpleTypeMock->expects($this->atLeastOnce())->method('isCollection');

        $this->assertTrue($this->genericType->isCollection());
    }

    function test_it_does_not_have_to_be_a_collection()
    {
        $this->assertFalse((new GenericType(\stdClass::class, [Type::STRING]))->isCollection());
    }

    function test_it_cannot_be_nullable()
    {
        $this->assertFalse($this->genericType->isNullable());
    }

    function test_it_is_generic()
    {
        $this->assertTrue($this->genericType->isGeneric());
    }

    function test_it_cannot_be_compound()
    {
        $this->assertFalse($this->genericType->isCompound());
    }

    function test_type_parameters_can_be_obtained()
    {
        $this->assertEquals([new SimpleType(Type::STRING)], $this->genericType->typeParameters());
        $this->assertEquals(
            [new SimpleType(Type::INTEGER), new SimpleType(Type::STRING)],
            $this->typedArray->typeParameters()
        );
    }

    function test_comparing_to_another_type()
    {
        $this->assertTrue($this->genericType->equals($this->genericType));
        $this->assertTrue($this->typedArray->equals(clone $this->typedArray));
        $typedArray = new GenericType(Type::ARRAY, [Type::INTEGER]);
        $this->assertFalse($this->typedArray->equals($typedArray));
        $this->assertFalse($this->genericType->equals($this->typedArray));
        $this->assertFalse($this->genericType->equals(\ArrayIterator::class));
    }

    function test_merging_with_same_type()
    {
        $this->assertTrue($this->genericType->merge($this->genericType)->equals($this->genericType));
    }

    function test_merging_with_compound_type_is_not_possible()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Merging of compound types is not supported.');

        $this->genericType->merge(new CompoundType(Type::STRING, Type::STRING));
    }

    function test_merging_with_generic_collection_having_same_type_parameters()
    {
        $genericCollection = new GenericType(\Traversable::class, [Type::STRING]);
        $this->simpleTypeMock->expects($this->atLeastOnce())->method('merge')->with($genericCollection);
        $merged = $this->genericType->merge($genericCollection);

        $this->assertSame(\Traversable::class, $merged->type());
        $this->assertEquals([new SimpleType(Type::STRING)], $merged->typeParameters());
    }

    function test_merging_with_generic_collection_having_compatible_type_parameters()
    {
        $merged = $this->typedArray->merge(new GenericType(Type::ARRAY, [Type::STRING, Type::INTEGER]));
        $this->assertSame(Type::ARRAY, $merged->type());
        $this->assertEquals([new SimpleType(Type::SCALAR), new SimpleType(Type::SCALAR)], $merged->typeParameters());
    }

    function test_merging_with_generic_collection_having_same_value_parameter()
    {
        $genericCollection = new GenericType(Type::ARRAY, [Type::STRING]);
        $merged = $this->typedArray->merge($genericCollection);

        $this->assertSame(Type::ARRAY, $merged->type());
        $this->assertEquals([new SimpleType(Type::STRING)], $merged->typeParameters());
    }

    function test_merging_with_generic_collection_having_compatible_value_parameter()
    {
        $genericCollection = new GenericType(Type::ARRAY, [Type::INTEGER]);
        $merged = $this->typedArray->merge($genericCollection);

        $this->assertSame(Type::ARRAY, $merged->type());
        $this->assertEquals([new SimpleType(Type::SCALAR)], $merged->typeParameters());
    }

    function test_merging_with_typed_array()
    {
        $merged = $this->genericType->merge($this->typedArray);
        $this->assertSame(\ArrayIterator::class, $merged->type());
        $this->assertEquals([new SimpleType(Type::STRING)], $merged->typeParameters());
    }

    function test_merging_with_generic_collection_having_different_parameters()
    {
        $merged = $this->typedArray->merge(new GenericType(Type::ARRAY, [Type::OBJECT, Type::RESOURCE]));
        $this->assertSame(Type::ARRAY, $merged->type());
        $this->assertEquals([], $merged->typeParameters());

        $merged = $this->typedArray->merge(new GenericType(Type::ARRAY, [Type::OBJECT]));
        $this->assertSame(Type::ARRAY, $merged->type());
        $this->assertEquals([], $merged->typeParameters());
    }

    function test_merging_with_a_different_type()
    {
        $simpleType = new SimpleType(Type::STRING);
        $this->simpleTypeMock->expects($this->atLeastOnce())->method('merge')->with($simpleType);

        $this->assertTrue($this->genericType->merge($simpleType)->equals(new SimpleType(Type::MIXED)));
    }

    function test_it_can_be_casted_to_string()
    {
        $this->assertSame('ArrayIterator<string>', (string) $this->genericType);
        $this->assertSame('array<int, string>', (string) $this->typedArray);
        $this->assertSame('Traversable<stdClass>', (string) new GenericType(\Traversable::class, [\stdClass::class]));
        $this->assertSame('string[]', (string) new GenericType(Type::ARRAY, [Type::STRING]));

        $genericType = new GenericType(Type::ARRAY, [new CompoundType(Type::INTEGER, Type::STRING)]);
        $this->assertSame('(int|string)[]', (string) $genericType);
    }
}
