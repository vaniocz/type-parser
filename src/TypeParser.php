<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class TypeParser implements Parser
{
    const ANNOTATION_VAR = 'var';
    const ANNOTATION_PARAM = 'param';
    const ANNOTATION_RETURN = 'return';

    /** @var TypeResolver */
    private $typeResolver;

    /** @var TypeContextFactory */
    private $contextFactory;

    /** @var array */
    private $propertyTypes = [];

    public function __construct(TypeResolver $typeResolver = null, TypeContextFactory $contextFactory = null)
    {
        $this->typeResolver = $typeResolver ?? new TypeResolver;
        $this->contextFactory = $contextFactory ?? new TypeContextFactory;
    }

    /**
     * @param object|string $class
     * @return Type[]
     */
    public function parsePropertyTypes($class): array
    {
        $class = is_object($class) ? get_class($class) : (string) $class;

        if (!isset($this->propertyTypes[$class])) {
            $this->propertyTypes[$class] = [];
            $reflectionClass = new \ReflectionClass($class);

            foreach ($reflectionClass->getProperties() as $property) {
                $propertyType = $this->parsePropertyType($property, $reflectionClass);
                $this->propertyTypes[$class][$property->name] = $propertyType;
            }
        }

        return $this->propertyTypes[$class];
    }

    /**
     * @param \ReflectionProperty $property
     * @param \ReflectionClass $class
     * @return Type|null
     */
    private function parsePropertyType(\ReflectionProperty $property, \ReflectionClass $class)
    {
        if (!$type = $this->parseTypeAnnotation($property, self::ANNOTATION_VAR)) {
            return null;
        }

        $context = $this->contextFactory->createContextFromClass($property->getDeclaringClass(), $class);

        return $this->typeResolver->resolveType($type, $context);
    }

    /**
     * @param \ReflectionProperty $property
     * @param string $type
     * @return string|null
     */
    private function parseTypeAnnotation(\ReflectionProperty $property, string $type)
    {
        if ($docComment = $property->getDocComment()) {
            preg_match(sprintf('~\s*%s\h+((?:[^@|<\s]+(?:<[^>@\v]+>)?(?:\h*\|\h*)?)+)~', $type), $docComment, $matches);
        }

        return $matches[1] ?? null;
    }
}
