<?php
namespace Vanio\TypeParser;

interface Parser
{
    /**
     * @param object|string $class
     * @return Type[]
     */
    function parsePropertyTypes($class): array;
}
