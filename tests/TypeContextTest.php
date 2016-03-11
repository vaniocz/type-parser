<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\TypeContext;

class TypeContextTest extends \PHPUnit_Framework_TestCase
{
    /** @var string[] */
    private $namespaceAliases = ['typecontext' => 'Vanio\TypeParser\TypeContext'];

    /** @var TypeContext */
    private $typeContext;

    protected function setUp()
    {
        $this->typeContext = TypeContext::fromClass(__CLASS__, null, $this->namespaceAliases);
    }

    function it_can_be_instantiated_using_namespace()
    {
        TypeContext::fromNamespace(__NAMESPACE__, $this->namespaceAliases);
    }

    function test_it_can_be_instantiated_using_class()
    {
        TypeContext::fromClass(new \ReflectionClass(__CLASS__));
        TypeContext::fromClass(__CLASS__, __CLASS__, $this->namespaceAliases);
    }

    function test_namespace_can_be_obtained()
    {
        $this->assertSame(__NAMESPACE__, TypeContext::fromNamespace(__NAMESPACE__)->namespaceName());
        $this->assertSame(__NAMESPACE__, $this->typeContext->namespaceName());
        $this->assertSame(__NAMESPACE__, TypeContext::fromClass(__CLASS__)->namespaceName());
        $this->assertSame(__NAMESPACE__, TypeContext::fromClass(__CLASS__, __CLASS__)->namespaceName());
    }

    function test_namespace_aliases_can_be_obtained()
    {
        $this->assertSame([], TypeContext::fromNamespace(__NAMESPACE__)->namespaceAliases());
        $this->assertSame($this->namespaceAliases, $this->typeContext->namespaceAliases());
    }

    function test_declaring_class_can_be_obtained()
    {
        $this->assertNull(TypeContext::fromNamespace(__NAMESPACE__)->declaringClass());
        $this->assertSame(__CLASS__, $this->typeContext->declaringClass()->name);
    }

    function test_declaring_class_name_can_be_obtained()
    {
        $this->assertNull(TypeContext::fromNamespace(__NAMESPACE__)->declaringClassName());
        $this->assertSame(__CLASS__, TypeContext::fromClass(__CLASS__)->declaringClassName());
    }

    function test_reflection_class_can_be_obtained()
    {
        $this->assertNull(TypeContext::fromNamespace(__NAMESPACE__)->reflectionClass());
        $this->assertSame(__CLASS__, $this->typeContext->reflectionClass()->name);

        $class = new \ReflectionClass(\stdClass::class);
        $this->assertSame(\stdClass::class, TypeContext::fromClass(__CLASS__, $class)->reflectionClass()->name);
    }

    function test_class_name_be_obtained()
    {
        $this->assertNull(TypeContext::fromNamespace(__NAMESPACE__)->className());
        $this->assertSame(__CLASS__, $this->typeContext->className());
        $this->assertSame(\stdClass::class, TypeContext::fromClass(__CLASS__, \stdClass::class)->className());
    }
}
