<?php
/***************************************************************************
 * 
 * Copyright (c) 2010 Baidu.com, Inc. All Rights Reserved
 * $Id: Writer.php,v 1.0 2013/04/19 16:35:28 songjingbo Exp $ 
 * 
 **************************************************************************/

/**
 * @file Writer.php
 * @author duwei<duwei04@baidu.com>
 * @date 2013/04/19 10:31:44
 * @version $Revision: 1.0 $ 
 * @brief class for logging
 *  
 **/


/**
 * 输出日志到标准输出
 * 主要是用在cli模式下，方便script开发调试
 * @author duwei
 */
class Fis_Log_Writer_Std implements Fis_Log_Writer{
   public function write($str){ 
    //给点颜色看吧
    $str = preg_replace("/^([FW]\w+)/", "\033[31m\\1\033[0m", $str);
    $str = preg_replace("/^([N]\w+)/", "\033[32m\\1\033[0m", $str);
    $str = preg_replace("/^([TD]\w+)/", "\033[33m\\1\033[0m", $str);
    $str = preg_replace("/(\w+)\=/", "\033[34m\\1\033[0m=", $str);
    echo urldecode($str);
   }
}
