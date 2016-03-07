<?php
namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser;

use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;

class EqualNamespacesPerFileWithClassAsFirst
{}

namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser;

use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Baz;
