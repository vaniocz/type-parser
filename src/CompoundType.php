<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class CompoundType implements Type, \IteratorAggregate
{
    /** @var Type[] */
    private $types;

    /** @var Type|null  */
    private $type;

    /** @var Type|null  */
    private $primaryType;

    /** @var bool */
    private $nullable = false;

    /**
     * @param Type[]|array ...$types
     */
    public function __construct(...$types)
    {
        foreach ($types as $type) {
            if (!$type instanceof Type) {
                $type = new SimpleType($type);
            }

            if ($type->isCompound()) {
                throw new \InvalidArgumentException('Compound type cannot be part of another compound type.');
            }

            $this->types[] = $type;
        }

        $this->mergeTypes();
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->types);
    }

    public function type(): string
    {
        return $this->type->type();
    }

    public function isScalar(): bool
    {
        return $this->type->isScalar();
    }

    public function isTypedObject(): bool
    {
        return $this->type->isTypedObject();
    }

    public function isCollection(): bool
    {
        return (bool) $this->type->isCollection();
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isGeneric(): bool
    {
        return $this->type->isGeneric();
    }

    public function isCompound(): bool
    {
        return true;
    }

    public function primaryType(): Type
    {
        return $this->primaryType;
    }

    /**
     * @return self[]
     */
    public function typeParameters(): array
    {
        return $this->type->typeParameters();
    }

    public function equals($type): bool
    {
        return $this === $type || $type instanceof self && !array_diff($this->types, iterator_to_array($type));
    }

    public function merge(Type $type): Type
    {
        throw new \InvalidArgumentException('Merging of compound types is not supported.');
    }

    public function __toString(): string
    {
        return implode('|', $this->types);
    }

    private function mergeTypes()
    {
        foreach ($this->types as $type) {
            if ($type->type() === self::NULL) {
                $this->nullable = true;
            } else {
                $this->type = $this->type ? $this->type->merge($type) : $type;
                $this->primaryType = $this->primaryType ?: $type->primaryType();
            }
        }

        if ($this->nullable && !$this->type) {
            $this->type = new SimpleType(self::NULL);
        }
    }
}
