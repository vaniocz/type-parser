<?php
namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Foo
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;
}

namespace
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;
}

namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Baz;

    class DifferentNamespacesPerFileWithClassAsLast
    {}
}
