<?php
namespace Vanio\TypeParser;

/**
 * @final
 */
class TypeContextFactory
{
    /** @var UseStatementsParser */
    private $parser;

    public function __construct(UseStatementsParser $parser)
    {
        $this->parser = $parser;
    }

    public function createContextFromNamespace(string $namespace, string $fileName): TypeContext
    {
        return TypeContext::fromNamespace($namespace, $this->parser->parseNamespace($namespace, $fileName));
    }

    /**
     * @param \ReflectionClass|string $declaringClass
     * @param \ReflectionClass|string|null $class
     * @return TypeContext
     */
    public function createContextFromClass($declaringClass, $class = null): TypeContext
    {
        $namespaceAliases = $this->parser->parseClass($declaringClass);

        return TypeContext::fromClass($declaringClass, $class ?? $declaringClass, $namespaceAliases);
    }
}
