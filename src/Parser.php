<?php
namespace Vanio\TypeParser;

interface Parser
{
    /**
     * @param string $class
     * @return Type[]
     */
    public function parsePropertyTypes(string $class): array;
}
