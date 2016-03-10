<?php
namespace Vanio\TypeParser;

use Doctrine\Common\Cache\CacheProvider;

class CachingParser implements Parser
{
    /** @var Parser */
    private $parser;

    /** @var CacheProvider */
    private $cache;

    /** @var Type[]|null */
    private $propertyTypes;

    /** @var bool */
    private $debug;

    /**
     * @param Parser $parser
     * @param CacheProvider $cache
     * @param bool $debug Whether to invalidate cache on file change (slower)
     */
    public function __construct(Parser $parser, CacheProvider $cache, bool $debug = true)
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
            $this->cache->setNamespace($this->resolveCacheNamespace($class));

            if (!$propertyTypes = $this->cache->fetch($class)) {
                $propertyTypes = $this->parser->parsePropertyTypes($class);
                $this->cache->save($class, $propertyTypes);
            }

            $this->propertyTypes[$class] = $propertyTypes;
        }

        return $this->propertyTypes[$class];
    }

    /**
     * @param string $class
     * @return int|bool
     */
    private function resolveCacheNamespace(string $class)
    {
        if (!$this->debug) {
            return __CLASS__;
        }

        $file = preg_replace('~\(\d+\) : eval\(\)\'d code$~', '', (new \ReflectionClass($class))->getFileName());

        return sprintf('%s[%s][%d]', __CLASS__, $file, @filemtime($file));
    }
}
