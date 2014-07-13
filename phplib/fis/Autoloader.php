<?php

/**
 * @file Autoloader.php
 * @author jpbirdy
 * @date
 * @brief
 *
 **/
class Fis_Autoloader
{
    private static $arrMap = null;

    /*
     * 添加类映射表
     *
     * @note: 文件若是相对路径，则自动加上ODP根目录，即ROOT_PATH宏
     * */
    public static function addClassMap($arrMap)
    {
        if (!self::$arrMap)
        {
            self::$arrMap = $arrMap;
            spl_autoload_register(array('Fis_Autoloader', 'autoload'));
        }
        else
        {
            self::$arrMap = array_merge(self::$arrMap, $arrMap);
        }
    }

    public static function reset()
    {
        spl_autoload_unregister(array('Fis_Autoloader', 'autoload'));
        self::$arrMap = null;
    }

    public static function autoload($name)
    {
        if (isset(self::$arrMap[$name]))
        {
            $file = self::$arrMap[$name];
            if ($file{0} == '/')
            {
                require_once $file;
            }
            else
            {
                require_once ROOT_PATH . "/$file";
            }
        }
    }
}
