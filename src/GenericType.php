<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class GenericType implements Type
{
    /** @var string */
    private $type;

    /** @var string */
    private $literal;

    /** @var Type[] */
    private $typeParameters = [];

    /**
     * @param SimpleType|string $type
     * @param Type[]|array $typeParameters
     */
    public function __construct($type, array $typeParameters = [])
    {
        if (!$type instanceof SimpleType) {
            $type = new SimpleType($type instanceof Type ? $type->type() : $type);
        }

        if (!$type->isTypedObject() && !$type->isCollection()) {
            throw new \InvalidArgumentException('Only typed objects or arrays can be generic.');
        } elseif (!$typeParameters) {
            throw new \InvalidArgumentException('Generic type needs to have at least one type parameter.');
        }

        $this->type = $type;
        $this->setTypeParameters($typeParameters);

        if ($type->type() === self::ARRAY && count($typeParameters) === 1) {
            $this->literal = $this->typeParameters[0]->isCompound()
                ? sprintf('(%s)[]', $this->typeParameters[0])
                : $this->typeParameters[0] . '[]';
        } else {
            $this->literal = $typeParameters ? sprintf('%s<%s>', $type, implode(', ', $typeParameters)) : $type;
        }
    }

    public function type(): string
    {
        return $this->type->type();
    }

    public function isScalar(): bool
    {
        return false;
    }

    public function isTypedObject(): bool
    {
        return $this->type->isTypedObject();
    }

    public function isCollection(): bool
    {
        return $this->type->isCollection();
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function isGeneric(): bool
    {
        return true;
    }

    public function isCompound(): bool
    {
        return false;
    }

    public function primaryType(): Type
    {
        return $this->type;
    }

    /**
     * @return SimpleType[]
     */
    public function typeParameters(): array
    {
        return $this->typeParameters;
    }

    public function equals($value): bool
    {
        return $value instanceof self && $this->literal === (string) $value;
    }

    public function merge(Type $type): Type
    {
        if ($this->equals($type)) {
            return $this;
        }

        if ($type->isCompound()) {
            return $type->merge($this);
        }

        $merged = $this->type->merge($type);

        if (!$type->isGeneric() || !$merged->isCollection() || !($typeParameters = $this->mergeTypeParameters($type))) {
            return $merged;
        }

        return new GenericType($merged, $typeParameters);
    }

    public function __toString(): string
    {
        return $this->literal;
    }

    /**
     * @param Type[]|array $typeParameters
     */
    private function setTypeParameters(array $typeParameters)
    {
        foreach ($typeParameters as $typeParameter) {
            $this->typeParameters[] = $typeParameter instanceof Type ? $typeParameter : new SimpleType($typeParameter);
        }
    }

    /**
     * @param Type $type
     * @return Type[]
     */
    private function mergeTypeParameters(Type $type): array
    {
        /** @var Type $thisKeyType */
        /** @var Type $thisValueType */
        list($thisKeyType, $thisValueType) = $this->typeParameters + [1 => null];
        list($typeKeyType, $typeValueType) = $type->typeParameters() + [1 => null];
        $mixed = new SimpleType(self::MIXED);

        if (!$thisValueType) {
            list($thisKeyType, $thisValueType) = [$mixed, $thisKeyType];
        }

        if (!$typeValueType) {
            list($typeKeyType, $typeValueType) = [$mixed, $typeKeyType];
        }

        $keyType = $thisKeyType->merge($typeKeyType);
        $valueType = $thisValueType->merge($typeValueType);
        $typeParameters = [$keyType, $valueType];

        if ($keyType->type() === self::MIXED) {
            if ($valueType->type() === self::MIXED) {
                return [];
            }

            $typeParameters = [$valueType];
        }

        return $typeParameters;
    }
}
