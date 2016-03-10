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
     * @param string $class
     * @return Type[]
     */
    public function parsePropertyTypes(string $class): array
    {
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
     * @return int|bool
     */
    private function resolveCacheId(string $class)
    {
        if ($this->debug) {
            $file = preg_replace('~\(\d+\) : eval\(\)\'d code$~', '', (new \ReflectionClass($class))->getFileName());
            $namespace = sprintf('%s[%s][%d]', __CLASS__, $file, @filemtime($file));
        } else {
            $namespace = __CLASS__;
        }

        return sprintf('%s[%s]', $namespace, $class);
    }
}
