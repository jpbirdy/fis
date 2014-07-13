<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Log_Manager {

    const LOGGER_DEPTH = 4;
    const SELF_LOG =1;
    const BD_LOG =2;
    const SELF_LOG_ENGINE_CLASS_NAME = 'SF_Log_LogEngine_SelfLogEngine';
    const BD_LOG_ENGINE_CLASS_NAME = 'SF_Log_LogEngine_BDLogEngine';

    private static $_isUnitTest = false;

    static function setUnitTestEnv()
    {
        self::$_isUnitTest = true;
    }

    static function unsetUnitTestEnv()
    {
        self::$_isUnitTest = false;
    }

    /**
     * @var array
     */
    private static $_logEngineContainer;


    /**
     * @desc 获取selfLogEngine的实例
     * @return SF_Interface_ILog
     */
    static function getSelfLoger()
    {
        return self::getLogInstance(self::SELF_LOG);
    }

    /**
     * @desc 获取BDlogEngine的实例
     * @param int $depth
     * @return SF_Interface_ILog
     */
    static function getBDLogger($depth = 3)
    {
        return self::getLogInstance(self::BD_LOG,$depth);
    }

    /**
     * @desc 获取LogEngine实例
     * @param string $type
     * @param int $depth
     * @return SF_Interface_ILog
     */
    static protected function getLogInstance($type,$depth = 2)
    {
        if(self::$_isUnitTest)
        {
            return new SF_Log_LogEngine_VardumpLogEngine();
        }

        switch($type)
        {
            case self::SELF_LOG:
                $engineClass = self::SELF_LOG_ENGINE_CLASS_NAME;
                break;
            case self::BD_LOG:
                $engineClass = self::BD_LOG_ENGINE_CLASS_NAME;
                break;
            default:
                $type = 'default';
                $engineClass = self::BD_LOG_ENGINE_CLASS_NAME;
        }
        return self::_getLogEngine($type, $engineClass, $depth);
    }

    /**
     * @param string $key
     * @param string $engineClass
     * @param int $depth 默认回溯深度
     * @return SF_Interface_ILog
     */
    private static function _getLogEngine($key, $engineClass, $depth = 1)
    {
        if (is_null(self::$_logEngineContainer) || !array_key_exists($key, self::$_logEngineContainer))
        {
            /**
             * @var SF_Interface_ILog $logEngineObj
             */
            $logEngineObj = new $engineClass();
            $logEngineObj->addDepth($depth);
            self::$_logEngineContainer [$key] = $logEngineObj;
        }
        return self::$_logEngineContainer[$key];
    }
}