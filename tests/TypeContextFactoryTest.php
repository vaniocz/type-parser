<?php
namespace Vanio\TypeParser\Tests;

use Vanio\TypeParser\TypeContextFactory;
use Vanio\TypeParser\UseStatementsParser;

class TypeContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var UseStatementsParser|\PHPUnit_Framework_MockObject_MockObject */
    private $parserMock;

    private $namespaceAliases = [
        'typecontextfactory' => 'Vanio\TypeParser\TypeContextFactory',
        'usestatementsparser' => 'Vanio\TypeParser\UseStatementsParser',
    ];

    protected function setUp()
    {
        $this->parserMock = $this->getMockWithoutInvokingTheOriginalConstructor(UseStatementsParser::class);
    }

    function test_creating_context_from_namespace()
    {
        $contextFactory = new TypeContextFactory($this->parserMock);
        $this->parserMock->expects($this->once())
            ->method('parseNamespace')
            ->with(__NAMESPACE__, __FILE__)
            ->willReturn($this->namespaceAliases);

        $context = $contextFactory->createContextFromNamespace(__NAMESPACE__, __FILE__);
        $this->assertSame(__NAMESPACE__, $context->namespaceName());
    }

    function test_creating_context_from_class()
    {
        $contextFactory = new TypeContextFactory($this->parserMock);
        $class = new \ReflectionClass(\stdClass::class);
        $this->parserMock->expects($this->any())
            ->method('parseClass')
            ->with(__CLASS__)
            ->willReturn($this->namespaceAliases);

        $context = $contextFactory->createContextFromClass(__CLASS__, $class);
        $this->assertSame(__NAMESPACE__, $context->namespaceName());
        $this->assertSame(__CLASS__, $context->declaringClassName());
        $this->assertSame(\stdClass::class, $context->className());

        $context = $contextFactory->createContextFromClass(__CLASS__, \stdClass::class);
        $this->assertSame(\stdClass::class, $context->className());
    }
}
