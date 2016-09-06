<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class SimpleType implements Type
{
    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        $type = ltrim($type, '\\');
        $this->type = self::TYPES[strtolower($type)] ?? $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isScalar(): bool
    {
        return in_array($this->type, [self::SCALAR, self::STRING, self::INTEGER, self::FLOAT, self::BOOLEAN]);
    }

    public function isTypedObject(): bool
    {
        return !(self::TYPES[strtolower($this->type)] ?? false);
    }

    public function isCollection(): bool
    {
        return $this->type === self::ARRAY || is_a($this->type, \Traversable::class, true);
    }

    public function isNullable(): bool
    {
        return in_array($this->type, [self::NULL, self::MIXED]);
    }

    public function isGeneric(): bool
    {
        return false;
    }

    public function isCompound(): bool
    {
        return false;
    }

    public function primaryType(): Type
    {
        return $this;
    }

    /**
     * @return Type[]
     */
    public function typeParameters(): array
    {
        return [];
    }

    public function equals($value): bool
    {
        return $value instanceof self && $this->type === $value->type();
    }

    public function merge(Type $type): Type
    {
        if ($this->equals($type)) {
            return $this;
        } elseif ($type->isCompound()) {
            return $type->merge($this);
        } elseif ($type->type() === self::NULL) {
            return $this->type === self::MIXED ? $this : new CompoundType($type, Type::NULL);
        } elseif ($this->isScalar() && $type->isScalar()) {
            return new self(self::SCALAR);
        } elseif ($type->isGeneric()) {
            return $type->type() === self::ARRAY && $this->type() !== Type::ARRAY && $this->isCollection()
                ? new GenericType($this, $type->typeParameters())
                : $type->merge($this);
        } elseif ($this->isTypedObject() && $type->isTypedObject()) {
            if ($this->type === $type->type() || is_a($type->type(), $this->type, true)) {
                return $this;
            } elseif (is_a($this->type, $type->type(), true)) {
                return $type;
            }
        }

        if ($this->isObject($this) && $this->isObject($type)) {
            return new SimpleType(self::OBJECT);
        }

        return new SimpleType(self::MIXED);
    }

    public function __toString(): string
    {
        return $this->type;
    }

    private function isObject(Type $type): bool
    {
        return $type->type() === self::OBJECT || $type->isTypedObject();
    }
}
