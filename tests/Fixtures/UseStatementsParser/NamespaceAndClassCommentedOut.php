<?php
// namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser;
namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Foo
{
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;

    // class NamespaceAndClassCommentedOut {}
}

namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser
{
    // class NamespaceAndClassCommentedOut {}
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;

    // namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser;
    use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;

    class NamespaceAndClassCommentedOut
    {}
}
