<?php

/**
 * 输出日志到标准输出
 * @author jpbirdy
 */
class Fis_Log_Writer_Std implements Fis_Log_Writer
{
    public function write($str)
    {
        //给点颜色看吧
        $str = preg_replace("/^([FW]\w+)/", "\033[31m\\1\033[0m", $str);
        $str = preg_replace("/^([N]\w+)/", "\033[32m\\1\033[0m", $str);
        $str = preg_replace("/^([TD]\w+)/", "\033[33m\\1\033[0m", $str);
        $str = preg_replace("/(\w+)\=/", "\033[34m\\1\033[0m=", $str);
        echo urldecode($str);
    }
}
