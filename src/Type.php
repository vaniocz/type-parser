<?php
namespace Vanio\TypeParser;

class Type
{
    const STRING = 'string';
    const INTEGER = 'int';
    const FLOAT = 'float';
    const BOOLEAN = 'bool';
    const ARRAY = 'array';
    const RESOURCE = 'resource';
    const NULL = 'null';
    const CALLABLE = 'callable';
    const MIXED = 'mixed';
    const VOID = 'void';
    const OBJECT = 'object';

    const TYPES = [
        'string' => self::STRING,
        'int' => self::INTEGER,
        'integer' => self::INTEGER,
        'number' => self::FLOAT,
        'float' => self::FLOAT,
        'bool' => self::BOOLEAN,
        'boolean' => self::BOOLEAN,
        'false' => self::BOOLEAN,
        'true' => self::BOOLEAN,
        'array' => self::ARRAY,
        'resource' => self::RESOURCE,
        'null' => self::NULL,
        'callable' => self::CALLABLE,
        'callback' => self::CALLABLE,
        'void' => self::VOID,
        'object' => self::OBJECT,
        'mixed' => self::MIXED,
    ];

    /** @var string */
    private $literal;

    /** @var bool */
    private $nullable = false;

    /** @var string[] */
    private $typeParameters;

    /**
     * @param string $type
     * @param bool $nullable
     * @param string[] $typeParameters
     */
    public function __construct(string $type, bool $nullable = false, array $typeParameters = [])
    {
        $type = self::TYPES[strtolower($type)] ?? $type;

        if ($type === self::ARRAY && count($typeParameters) === 1) {
            $this->literal = current($typeParameters) . '[]';
        } else {
            $this->literal = $typeParameters ? sprintf('%s<%s>', $type, implode(', ', $typeParameters)) : $type;
        }

        if (in_array($type, [self::NULL, self::MIXED])) {
            $this->nullable = true;
        } elseif ($nullable) {
            $this->nullable = true;
            $this->literal .= '|null';
        }

        $this->type = $type;
        $this->typeParameters = $typeParameters;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return string[]
     */
    public function typeParameters(): array
    {
        return $this->typeParameters;
    }

    public function isGeneric(): bool
    {
        return (bool) $this->typeParameters;
    }

    public function __toString(): string
    {
        return $this->literal;
    }
}
