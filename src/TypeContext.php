<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class TypeContext
{
    /** @var string */
    private $namespace;

    /** @var \ReflectionClass|null */
    private $declaringClass;

    /** @var \ReflectionClass|null */
    private $class;

    /** @var string[] */
    private $namespaceAliases;

    /**
     * @param string $namespace
     * @param array $namespaceAliases
     * @return self
     */
    public static function fromNamespace(string $namespace, array $namespaceAliases = []): self
    {
        return new self($namespace, $namespaceAliases);
    }

    /**
     * @param \ReflectionClass|string $declaringClass
     * @param \ReflectionClass|string|null $class
     * @param string[] $namespaceAliases
     * @return self
     */
    public static function fromClass($declaringClass, $class = null, array $namespaceAliases = []): self
    {
        if (!$declaringClass instanceof \ReflectionClass) {
            $declaringClass = new \ReflectionClass($declaringClass);
        }

        if (!$class) {
            $class = $declaringClass;
        } elseif (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
        }

        return new self($declaringClass->getNamespaceName(), $namespaceAliases, $declaringClass, $class);
    }

    /**
     * @param string $namespace
     * @param string[] $namespaceAliases
     * @param \ReflectionClass|null $declaringClass
     * @param \ReflectionClass|null $class
     */
    private function __construct(
        string $namespace,
        array $namespaceAliases = [],
        \ReflectionClass $declaringClass = null,
        \ReflectionClass $class = null
    ) {
        $this->namespace = ltrim($namespace, '\\');
        $this->namespaceAliases = $namespaceAliases;
        $this->declaringClass = $declaringClass;
        $this->class = $class;
    }

    public function namespaceName(): string
    {
        return $this->namespace;
    }

    /**
     * @return string[]
     */
    public function namespaceAliases(): array
    {
        return $this->namespaceAliases;
    }

    /**
     * @return \ReflectionClass|null
     */
    public function declaringClass()
    {
        return $this->declaringClass;
    }

    /**
     * @return \ReflectionClass|null
     */
    public function declaringClassName()
    {
        return $this->declaringClass ? $this->declaringClass->name : null;
    }

    /**
     * @return \ReflectionClass|null
     */
    public function reflectionClass()
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function className()
    {
        return $this->class ? $this->class->name : null;
    }
}
