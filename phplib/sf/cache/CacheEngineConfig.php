<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Cache_CacheEngineConfig {
    /**
     * @desc 返回当前引擎配置的类型
     * @return int
     */
    abstract public function getType();

    /**
     * @desc 在该方法中编写，如何使用配置项
     * @param SF_Cache_CacheEngineBase $cacheEngine
     * @return void
     */
    abstract function injectEngineConfig(SF_Cache_CacheEngineBase $cacheEngine);
}