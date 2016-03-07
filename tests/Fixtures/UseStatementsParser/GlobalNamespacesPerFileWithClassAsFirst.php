<?php
namespace
{
	use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;
	use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;

	class Vanio_TypeParser_Tests_Fixtures_UseStatementsParser_GlobalNamespacesPerFileWithClassAsFirst
    {}
}

namespace
{
	use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Baz;
}

namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Qux;
}
