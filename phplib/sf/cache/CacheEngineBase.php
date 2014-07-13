<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Cache_CacheEngineBase  implements SF_Interface_ICacheOperate{

    /**
     * @desc 初始化CacheEngine
     * @param SF_Cache_CacheEngineConfig $engineConfig
     */
    function __construct(SF_Cache_CacheEngineConfig $engineConfig)
    {
        $this->setEngineConfig($engineConfig);
    }

    /**
     * @desc 将CacheEngine相关的配置注入
     * @param SF_Cache_CacheEngineConfig $engineConfig
     */
    protected function setEngineConfig(SF_Cache_CacheEngineConfig $engineConfig)
    {
        $engineConfig->injectEngineConfig($this);
    }
} 