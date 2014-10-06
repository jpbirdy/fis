<?php

/**
 * @file Base.php
 * @author jpbirdy
 * @date 2014年7月14日16:24:01
 * @brief 自动根据路径读取配置文件。默认配置文件为conf/global.conf
 * 出现同名情况，文件覆盖global.conf配置，文件夹覆盖文件配置
 **/
class Fis_Conf_Base
{
    private static  $_settings ;
    protected static $_conf_path;

    /**
     * 获取键值
     */
    public static function get($var)
    {
        self::$_conf_path = Fis_Appenv::getEnv('conf');
        $var = explode ( '/', $var );
        $var = self::getRealItem($var,self::$_conf_path);

        $result = self::$_settings;
        foreach ( $var as $key )
        {
            if (! isset ( $result [$key] ))
            {
                return false;
            }
            $result = $result [$key];
        }
        return $result;
    }

    public static function load($file)
    {
        if (file_exists ( $file ) == false)
        {
            return false;
        }
        self::$_settings = parse_ini_file ( $file, true );
    }

    /**
     * 获取真实的item项
     * @param $var
     * @param $conf_path
     * @return array
     */
    public static function getRealItem($var , $conf_path)
    {
        $item = array();
        while(count($var) > 0)
        {
            $filepath = $conf_path . '/' . implode('/',$var) . '.conf';
            if (file_exists ( $filepath ) )
            {
                self::load($filepath);
                break;
            }
            array_push($item,array_pop($var));
        }
        if(count($var) == 0)
        {
            self::load($conf_path . '/global.conf');
        }
        return array_reverse($item);
    }
}