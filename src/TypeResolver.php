<?php
namespace Vanio\TypeParser;

class TypeResolver
{
    public function resolveType(string $type, TypeContext $context): Type
    {
        if ($this->isKeyword($type)) {
            return $this->resolveKeyword($type, $context);
        } elseif ($this->isCompoundType($type)) {
            return $this->resolveCompoundType($type, $context);
        } elseif ($this->isTypedArray($type)) {
            return $this->resolveTypedArray($type, $context);
        } elseif ($this->isGenericType($type)) {
            return $this->resolveGenericType($type, $context);
        }

        return $this->resolveClass($type, $context);
    }

    private function isKeyword(string $type): bool
    {
        return Type::TYPES[$this->normalizeKeyword($type)] ?? false;
    }

    private function resolveKeyword(string $keyword, TypeContext $context): Type
    {
        $keyword = $this->normalizeKeyword($keyword);

        if (in_array($keyword, ['self', 'static', '$this'])) {
            $keyword = $keyword === 'self'
                ? $context->declaringClassName()
                : $context->className() ?? $keyword;
        } else {
            $keyword = Type::TYPES[$keyword];
        }

        return new SimpleType($keyword);
    }

    private function isCompoundType(string $type): bool
    {
        return preg_match('~\|(?!.*\))~', $type);
    }

    private function resolveCompoundType(string $type, TypeContext $context): CompoundType
    {
        $types = [];

        foreach (preg_split('~\h*\|(?!\h*\))\h*~', $type, -1, PREG_SPLIT_NO_EMPTY) as $type) {
            $types[] = $this->resolveType($type, $context);
        }

        return new CompoundType(...$types);
    }

    private function isTypedArray(string $type): bool
    {
        return substr($type, -2) === '[]';
    }

    private function resolveTypedArray(string $typedArray, TypeContext $context): Type
    {
        $parenthesised = $typedArray[0] === '(';
        $type = substr($typedArray, 0 + $parenthesised, -2 - $parenthesised);

        return new GenericType(Type::ARRAY, [$this->resolveType($type, $context)]);
    }

    private function isGenericType(string $type): bool
    {
        return strpos($type, '<') !== false;
    }

    private function resolveGenericType(string $genericType, TypeContext $context): Type
    {
        list($genericType, $typeParametersLiteral) = explode('<', $genericType, 2);
        $typeParameters = $typeParametersLiteral
            ? preg_split('~,\h*~', trim(substr($typeParametersLiteral, 0, -1)))
            : [];

        foreach ($typeParameters as $i => $typeParameter) {
            $typeParameters[$i] = $this->resolveType($typeParameter,$context);
        }

        $genericType = !strcmp($genericType, Type::ARRAY)
            ? Type::ARRAY
            : $this->resolveClassName($genericType, $context);

        return new GenericType($genericType, $typeParameters);
    }

    private function resolveClass(string $class, TypeContext $context): Type
    {
        return new SimpleType($this->resolveClassName($class, $context));
    }

    private function resolveClassName(string $class, TypeContext $context): string
    {
        if (substr($class, 0, 1) === '\\') {
            return ltrim($class, '\\');
        }

        list($namespace, $className) = explode('\\', $class) + [1 => null];

        if ($alias = $context->namespaceAliases()[strtolower($namespace)] ?? null) {
            return $className ? sprintf('%s\%s', $alias, $className) : $alias;
        }

        return sprintf('%s\%s', $context->namespace(), $class);
    }

    private function normalizeKeyword(string $keyword): string
    {
        return strtolower(ltrim($keyword, '\\'));
    }
}
