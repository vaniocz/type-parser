<?php
namespace Vanio\TypeParser\Tests;

use PHPUnit\Framework\TestCase;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\ClassUsesTrait;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\ClassWithClosureDeclaration;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\ClassWithFullyQualifiedUseStatements;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\DifferentNamespacesPerFileWithClassAsFirst;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\DifferentNamespacesPerFileWithClassAsLast;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\EqualNamespacesPerFileWithClassAsFirst;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\EqualNamespacesPerFileWithClassAsLast;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\MultipleClassesInFile;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\MultipleImportsInUseStatement;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\NamespaceAndClassCommentedOut;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\NamespaceWithClosureDeclaration;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\TestInterface;
use Vanio\TypeParser\UseStatementsParser;

const FIXTURES_DIRECTORY = __DIR__ . '/Fixtures/UseStatementsParser/';

require_once FIXTURES_DIRECTORY . 'NonNamespacedClass.php';
require_once FIXTURES_DIRECTORY . 'GlobalNamespacesPerFileWithClassAsFirst.php';
require_once FIXTURES_DIRECTORY . 'GlobalNamespacesPerFileWithClassAsLast.php';

class UseStatementsParserTest extends TestCase
{
    const FIXTURES_NAMESPACE = __NAMESPACE__ . '\Fixtures\UseStatementsParser';

    /** @var UseStatementsParser */
    private $parser;

    protected function setUp()
    {
        $this->parser = new UseStatementsParser;
    }

    public function test_parsing_class_with_multiple_classes_in_file()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(MultipleClassesInFile::class)
        );
    }

    public function test_parsing_class_with_multiple_imports_in_use_statement()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(MultipleImportsInUseStatement::class)
        );
    }

    public function test_parsing_class_when_not_user_defined()
    {
        $this->assertSame([], $this->parser->parseClass(\stdClass::class));
    }

    public function test_parsing_class_when_file_does_not_exist()
    {
        $classMock = $this->getMockBuilder('\ReflectionClass')->disableOriginalConstructor()->getMock();
        $classMock->expects($this->once())
            ->method('getFilename')
            ->willReturn('/valid/class/file.php(1) : eval()d code');
        $classMock->expects($this->once())
            ->method('getNamespacename')
            ->willReturn(__NAMESPACE__);

        $this->assertSame([], $this->parser->parseClass($classMock));
    }

    public function test_parsing_class_when_it_is_not_namespaced()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(\Vanio_TypeParser_Tests_Fixtures_UseStatementsParser_NonNamespacedClass::class)
        );
    }

    public function test_parsing_class_when_it_is_interface()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(TestInterface::class)
        );
    }

    public function test_parsing_class_with_fully_qualified_use_statements()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
                'baz' => self::FIXTURES_NAMESPACE . '\Import\Baz',
            ],
            $this->parser->parseClass(ClassWithFullyQualifiedUseStatements::class)
        );
    }

    public function test_parsing_class_with_namespace_and_class_commented_out()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(NamespaceAndClassCommentedOut::class)
        );
    }

    public function test_parsing_class_with_equal_namespaces_per_file_having_class_as_first()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(EqualNamespacesPerFileWithClassAsFirst::class)
        );
    }

    public function test_parsing_class_with_equal_namespaces_per_file_having_class_as_last()
    {
        $this->assertSame(
            [
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
                'baz' => self::FIXTURES_NAMESPACE . '\Import\Baz',
            ],
            $this->parser->parseClass(EqualNamespacesPerFileWithClassAsLast::class)
        );
    }

    public function test_parsing_class_with_different_namespaces_per_file_having_class_as_first()
    {
        $this->assertSame(
            ['foo' => self::FIXTURES_NAMESPACE . '\Import\Foo'],
            $this->parser->parseClass(DifferentNamespacesPerFileWithClassAsFirst::class)
        );
    }

    public function test_parsing_class_with_different_namespaces_per_file_having_class_as_last()
    {
        $this->assertSame(
            ['baz' => self::FIXTURES_NAMESPACE . '\Import\Baz'],
            $this->parser->parseClass(DifferentNamespacesPerFileWithClassAsLast::class)
        );
    }

    public function test_parsing_class_with_global_namespaces_per_file_having_class_as_first()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(\Vanio_TypeParser_Tests_Fixtures_UseStatementsParser_GlobalNamespacesPerFileWithClassAsFirst::class)
        );
    }

    public function test_parsing_class_with_global_namespaces_per_file_having_class_as_last()
    {
        $this->assertSame(
            [
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
                'baz' => self::FIXTURES_NAMESPACE . '\Import\Baz',
            ],
            $this->parser->parseClass(\Vanio_TypeParser_Tests_Fixtures_UseStatementsParser_GlobalNamespacesPerFileWithClassAsLast::class)
        );
    }

    public function test_parsing_class_with_namespace_having_closure_declaration()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(NamespaceWithClosureDeclaration::class)
        );
    }

    public function test_parsing_class_with_closure_declaration()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(ClassWithClosureDeclaration::class)
        );
    }

    public function test_parsing_class_which_uses_trait()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseClass(ClassUsesTrait::class)
        );
    }

    public function test_parsing_namespace_with_multiple_classes_in_file()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'MultipleClassesInFile.php'
            )
        );
    }

    public function test_parsing_namespace_with_multiple_imports_in_use_statement()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'MultipleImportsInUseStatement.php'
            )
        );
    }

    public function test_parsing_namespace_when_file_does_not_exist()
    {
        $this->assertSame([], $this->parser->parseNamespace(self::FIXTURES_NAMESPACE, 'non/existent/file'));
    }

    public function test_parsing_namespace_when_it_is_not_namespaced()
    {
        $this->assertSame(
            [],
            $this->parser->parseNamespace(self::FIXTURES_NAMESPACE, FIXTURES_DIRECTORY . 'NonNamespacedClass.php')
        );
    }

    public function test_parsing_namespace_with_equal_namespaces_per_file()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
                'baz' => self::FIXTURES_NAMESPACE . '\Import\Baz',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'EqualNamespacesPerFileWithClassAsFirst.php'
            )
        );
    }

    public function test_parsing_namespace_with_global_namespaces_per_file()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
                'baz' => self::FIXTURES_NAMESPACE . '\Import\Baz',
            ],
            $this->parser->parseNamespace('', FIXTURES_DIRECTORY . 'GlobalNamespacesPerFileWithClassAsFirst.php')
        );
    }

    public function test_parsing_namespace_with_closure_declaration()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'NamespaceWithClosureDeclaration.php'
            )
        );
    }

    public function test_parsing_namespace_with_class_having_closure_declaration()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'ClassWithClosureDeclaration.php'
            )
        );
    }

    public function test_parsing_namespace_with_class_which_uses_trait()
    {
        $this->assertSame(
            [
                'foo' => self::FIXTURES_NAMESPACE . '\Import\Foo',
                'bar' => self::FIXTURES_NAMESPACE . '\Import\Bar',
            ],
            $this->parser->parseNamespace(
                self::FIXTURES_NAMESPACE,
                FIXTURES_DIRECTORY . 'ClassUsesTrait.php'
            )
        );
    }
}
