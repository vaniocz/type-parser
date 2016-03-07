<?php
namespace Vanio\TypeParser\Tests\Fixtures;

use \ArrayIterator;
use ArrayIterator as Iterator;
use Vanio\TypeParser\Type;
use Vanio\TypeParser\TypeParser as Parser;

class Foo
{
    /**
     * Comment
     * @deprecated
     * @var ArrayIterator an array iterator
     * @internal
     */
    public $arrayIterator;

    /** @var Parser */
    public $typeParser;

    /** @var Type|null */
    public $nullableType;

    /** @var ArrayIterator<Type> */
    public $typeIterator;

    /** @var ArrayIterator<Type>|Iterator<\Vanio\TypeParser\Type> */
    public $preciselyMergedTypeIterator;

    /** @var Iterator<Type>|Iterator<mixed> */
    public $roughlyMergedTypeIterator;

    /** @var Iterator|Type[]|null */
    public $nullableTypeIterator;

    /** @var array< int, Type > */
    public $genericArray;

    /** @var int | string */
    public $scalar;

    /** @var int|string|object */
    public $mixed;

    /** @var int */
    protected $extended;

    /** @var STRING */
    private $string;

    /**
     * @var
     * ArrayIterator
     */
    private $invalidDocBlock;
}
