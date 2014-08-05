<?php

/**
 * @name Controller_Index
 * @desc 主控制器
 */
class Controller_Index extends Yaf_Controller_Abstract
{
    public $actions = array(
        'sample' => 'actions/Sample.php',
        'call' => 'actions/Call.php',
        'session' => 'actions/Session.php',
        'redis' => 'actions/Redis.php',
    );
}
