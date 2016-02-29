<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\Type;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    function test_it_has_type()
    {
        $type = new Type(Type::STRING);
        $this->assertSame(Type::STRING, $type->type());

        $type = new Type('STRING');
        $this->assertSame(Type::STRING, $type->type());

        $type = new Type(\stdClass::class);
        $this->assertSame(\stdClass::class, $type->type());
    }

    function test_it_is_nullable()
    {
        $type = new Type(Type::STRING, true);
        $this->assertTrue($type->isNullable());

        $type = new Type(Type::NULL);
        $this->assertTrue($type->isNullable());

        $type = new Type(Type::MIXED);
        $this->assertTrue($type->isNullable());
    }

    function test_it_is_not_nullable()
    {
        $type = new Type(Type::STRING, false);
        $this->assertFalse($type->isNullable());
    }

    function test_it_has_type_parameters()
    {
        $type = new Type(Type::ARRAY, false, [Type::STRING]);
        $this->assertSame([Type::STRING], $type->typeParameters());

        $type = new Type(Type::ARRAY, false, [Type::INTEGER, Type::STRING]);
        $this->assertEquals([Type::INTEGER, Type::STRING], $type->typeParameters());
    }

    function test_it_is_generic()
    {
        $type = new Type(Type::ARRAY, false, [Type::STRING]);
        $this->assertTrue($type->isGeneric());
    }

    function test_it_is_not_generic()
    {
        $type = new Type(Type::STRING);
        $this->assertFalse($type->isGeneric());
    }

    function test_it_casts_to_string()
    {
        $this->assertSame('string', (string) new Type(Type::STRING));
        $this->assertSame('string', (string) new Type('STRING'));
        $this->assertSame('type', (string) new Type('type'));
        $this->assertSame('string|null', (string) new Type(Type::STRING, true));
        $this->assertSame('string[]|null', (string) new Type(Type::ARRAY, true, [Type::STRING]));
        $this->assertSame('array<int, string>', (string) new Type(Type::ARRAY, false, [Type::INTEGER, Type::STRING]));
    }
}
