<?php
/**
 * @file Conf.php
 * @author jpbirdy
 * @date 2014年7月14日16:23:34
 * @brief 待实现全局配置
 *
 **/
class Fis_Conf extends Fis_Conf_Base
{
    public static function getConf($item = null, $app = null)
    {
        return self::get($item);
    }

    public static function getAppConf($item = null, $app = null)
    {
        return self::get($item);
    }
}

