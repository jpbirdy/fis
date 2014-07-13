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
 * 
 * @author duwei<duwei04@baidu.com>
 */
interface  Fis_Log_Writer{
   public function write($str);
}
