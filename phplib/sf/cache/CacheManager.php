<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Cache_CacheManager{


    const REDIS_CACHE = 1;
    const MEMCACHE_CACHE = 2;
    const REDIS_REDIS_ENGINE_CLASS_NAME = 'SF_Cache_CacheEngine_RedisCacheEngine';
    const MEMCACHE_ENGINE_CLASS_NAME = 'SF_Cache_CacheEngine_MemcacheEngine';

    /**
     * @var array
     */
    private static $_cacheEngineContainer;

    /**
     * @param SF_Cache_CacheEngineConfig $engineConfig
     * @return SF_Interface_ICacheOperate
     */
    public static function getCacheEngine($engineConfig)
    {
        $type = $engineConfig->getType();
        switch($type)
        {
            case self::REDIS_CACHE:
                $engineClass = self::REDIS_REDIS_ENGINE_CLASS_NAME;
                break;
            case self::MEMCACHE_CACHE:
                $engineClass = self::MEMCACHE_ENGINE_CLASS_NAME;
                break;
            default:
                $engineClass = self::REDIS_REDIS_ENGINE_CLASS_NAME;
                $type = 'default';
        }
        return self::_getCacheEngine($type, $engineClass, $engineConfig);
    }

    /**
     * @desc 获取对应的CacheEngine
     * @param $key
     * @param $engineClass
     * @param $engineConfig
     * @return SF_Interface_ICacheOperate
     */
    private static function _getCacheEngine($key, $engineClass, $engineConfig)
    {
        if (!array_key_exists($key, self::$_cacheEngineContainer))
        {
            $cacheEngineObj = new $engineClass($engineConfig);
            self::$_cacheEngineContainer [$key] = $cacheEngineObj;
        }
        return self::$_cacheEngineContainer[$key];
    }

}