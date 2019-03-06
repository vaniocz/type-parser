<?php
namespace Vanio\TypeParser;

use Doctrine\Common\Cache\Cache;

class CachingParser implements Parser
{
    /** @var Parser */
    private $parser;

    /** @var Cache */
    private $cache;

    /** @var Type[]|null */
    private $propertyTypes;

    /** @var bool */
    private $debug;

    /**
     * @param Parser $parser
     * @param Cache $cache
     * @param bool $debug Whether to invalidate cache on file change (slower)
     */
    public function __construct(Parser $parser, Cache $cache, bool $debug = true)
    {
        $this->parser = $parser;
        $this->cache = $cache;
        $this->debug = $debug;
    }

    /**
     * @param object|string $class
     * @return Type[]
     */
    public function parsePropertyTypes($class): array
    {
        $class = is_object($class) ? get_class($class) : (string) $class;

        if (!isset($this->propertyTypes[$class])) {
            $cacheId = $this->resolveCacheId($class);

            if (!$propertyTypes = $this->cache->fetch($cacheId)) {
                $propertyTypes = $this->parser->parsePropertyTypes($class);
                $this->cache->save($cacheId, $propertyTypes);
            }

            $this->propertyTypes[$class] = $propertyTypes;
        }

        return $this->propertyTypes[$class];
    }

    /**
     * @param string $class
     * @return string
     */
    private function resolveCacheId(string $class): string
    {
        if (!$this->debug) {
            return sprintf('%s[%s]', __CLASS__, $class);
        }

        $reflectionClass = new \ReflectionClass($class);
        $file = preg_replace('~\(\d+\) : eval\(\)\'d code$~', '', $reflectionClass->getFileName());
        $modificationTimes = [];

        do {
            $modificationTimes[] = @filemtime($reflectionClass->getFileName());

            foreach ($reflectionClass->getTraits() as $reflectionTrait) {
                $modificationTimes[] = @filemtime($reflectionTrait->getFileName());
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return sprintf('%s[%s][%s][%s]', __CLASS__, $file, implode(',', $modificationTimes), $class);
    }
}
