<?php
namespace Vanio\TypeParser;

interface Type
{
    const VOID = 'void';
    const NULL = 'null';
    const STRING = 'string';
    const INTEGER = 'int';
    const FLOAT = 'float';
    const BOOLEAN = 'bool';
    const SCALAR = 'scalar';
    const ARRAY = 'array';
    const OBJECT = 'object';
    const CALLABLE = 'callable';
    const RESOURCE = 'resource';
    const MIXED = 'mixed';

    const TYPES = [
        'void' => self::VOID,
        'null' => self::NULL,
        'string' => self::STRING,
        'int' => self::INTEGER,
        'integer' => self::INTEGER,
        'float' => self::FLOAT,
        'double' => self::FLOAT,
        'number' => self::FLOAT,
        'bool' => self::BOOLEAN,
        'boolean' => self::BOOLEAN,
        'false' => self::BOOLEAN,
        'true' => self::BOOLEAN,
        'scalar' => self::SCALAR,
        'array' => self::ARRAY,
        'resource' => self::RESOURCE,
        'callable' => self::CALLABLE,
        'callback' => self::CALLABLE,
        'object' => self::OBJECT,
        'self' => self::OBJECT,
        'static' => self::OBJECT,
        '$this' => self::OBJECT,
        'mixed' => self::MIXED,
    ];

    function type(): string;

    function isScalar(): bool;

    function isTypedObject(): bool;

    function isCollection(): bool;

    function isNullable(): bool;

    function isGeneric(): bool;

    function isCompound(): bool;

    function primaryType(): self;

    /**
     * @return self[]
     */
    function typeParameters(): array;

    /**
     * @param mixed $value
     * @return bool
     */
    function equals($value): bool;

    function merge(Type $type): self;

    function __toString(): string;
}
