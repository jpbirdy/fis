<?php
/***************************************************************************
 * 
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
 
 
/**
 * @file index.php
 * @author 
 * @date 14-3-19
 * @brief 入口文件
 **/
$objApplication = Fis_Init::init();
$objResponse = $objApplication->bootstrap()->run();