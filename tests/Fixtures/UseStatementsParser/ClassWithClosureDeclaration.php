<?php
namespace Vanio\TypeParser\Tests\Fixtures\UseStatementsParser;

use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Foo;
use Vanio\TypeParser\Tests\Fixtures\UseStatementsParser\Import\Bar;

class ClassWithClosureDeclaration
{
    public function __construct()
    {
        $var = 1;
        function () use ($var) {};
    }
}
