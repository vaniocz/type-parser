<?php
namespace Vanio\TypeParser;

interface Parser
{
    /**
     * @param string $class
     * @return Type[]
     */
    function parsePropertyTypes(string $class): array;
}
