<?php
namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;

    class DifferentNamespacesPerFileWithClassAsFirst
    {}
}

namespace
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;
}

namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Foo
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Baz;
}
