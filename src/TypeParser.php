<?php
namespace Vanio\TypeParser;

use Doctrine\Common\Annotations\PhpParser;

class TypeParser implements Parser
{
    /** @var PhpParser */
    private $phpParser;

    /** @var string[]|null */
    private $useStatements;

    /** @var array */
    private $propertyTypes;

    public function __construct(PhpParser $phpParser)
    {
        $this->phpParser = $phpParser;
    }

    /**
     * @param string $class
     * @return Type[]
     */
    public function parsePropertyTypes(string $class): array
    {
        if (!isset($this->propertyTypes[$class])) {
            $this->propertyTypes[$class] = [];

            foreach ((new \ReflectionClass($class))->getProperties() as $property) {
                if ($type = $this->parsePropertyType($property, $class)) {
                    $this->propertyTypes[$class][$property->name] = $type;
                }
            }
        }

        return $this->propertyTypes[$class];
    }

    /**
     * @param \ReflectionProperty $property
     * @param string $class
     * @return Type|null
     */
    private function parsePropertyType(\ReflectionProperty $property, string $class)
    {
        if (!$types = $this->parseVarAnnotation($property)) {
            return null;
        }

        $propertyType = Type::NULL;
        $propertyTypeParameters = [];
        $nullable = false;
        $i = 0;

        foreach ($types as $type) {
            if (!strcmp($type, Type::NULL)) {
                $nullable = true;
                continue;
            } elseif (++$i === 1) {
                list($propertyType, $propertyTypeParameters) = $this->parseGenericType($property, $class, $type);
            } elseif (
                $i === 2 && !$propertyTypeParameters
                && $this->isGenericArray($type) && is_a($propertyType, \Traversable::class, true)
            ) {
                $propertyTypeParameters = $this->parseGenericType($property, $class, $type)[1];
            } elseif ($propertyType !== Type::MIXED) {
                list($type, $typeParameters) = $this->parseGenericType($property, $class, $type);

                if ($propertyType !== $type) {
                    $propertyType = Type::MIXED;
                } elseif ($propertyTypeParameters !== $typeParameters) {
                    $propertyTypeParameters = [];
                }
            } elseif ($nullable) {
                break;
            }
        }

        return new Type($propertyType, $nullable, $propertyTypeParameters);
    }

    /**
     * @param \ReflectionProperty $property
     * @return string[]
     */
    private function parseVarAnnotation(\ReflectionProperty $property)
    {
        if ($docComment = $property->getDocComment()) {
            preg_match('~\s*@var\h+((?:[^@|<\s]+(?:<[^>@\v]+>)?(?:\h*\|\h*)?)+)~', $docComment, $matches);
        }

        return isset($matches[1]) ? preg_split('~\h*\|\h*~', $matches[1], -1, PREG_SPLIT_NO_EMPTY) : [];
    }

    private function parseGenericType(\ReflectionProperty $property, string $class, string $type): array
    {
        if ($this->isGenericArray($type)) {
            $typeParameters = [substr($type, 0, -2)];
            $type = Type::ARRAY;
        } else {
            list($type, $typeParametersLiteral) = explode('<', $type, 2) + [1 => null];
            $typeParameters = $typeParametersLiteral
                ? preg_split('~,\h*~', trim(substr($typeParametersLiteral, 0, -1)))
                : [];
        }

        foreach ($typeParameters as $i => $typeParameter) {
            $typeParameters[$i] = $this->resolveFullyQualifiedName($property, $class, $typeParameter);
        }

        return [$this->resolveFullyQualifiedName($property, $class, $type), $typeParameters];
    }

    private function isGenericArray(string $type): bool
    {
        return substr($type, -2) === '[]';
    }

    private function resolveFullyQualifiedName(\ReflectionProperty $property, string $class, string $type): string
    {
        if ($isGlobal = $type[0] === '\\') {
            $type = substr($type, 1);
        }

        $reflectionClass = $property->getDeclaringClass();
        $loweredTyped = strtolower($type);

        if (Type::TYPES[$loweredTyped] ?? false) {
            return Type::TYPES[$loweredTyped];
        } elseif (in_array($type, ['self', 'static', '$this'])) {
            return strcmp($type, 'self') ? $class : $property->class;
        } elseif ($isGlobal) {
            return $type;
        } elseif ($fullyQualifiedName = $this->parseUseStatements($reflectionClass)[$loweredTyped] ?? null) {
            return $fullyQualifiedName[0] === '\\' ? substr($fullyQualifiedName, 1) : $fullyQualifiedName;
        } elseif ($namespace = $reflectionClass->getNamespaceName()) {
            return sprintf('%s\%s', $namespace, $type);
        }

        return $type;
    }

    /**
     * @param \ReflectionClass $class
     * @return string[]
     */
    private function parseUseStatements(\ReflectionClass $class): array
    {
        if (!isset($this->useStatements[$class->name])) {
            $this->useStatements[$class->name] = $this->phpParser->parseClass($class);
        }

        return $this->useStatements[$class->name];
    }
}
