<?php

/**
 * @name Controller_Index
 * @desc 主控制器
 */
class Controller_Test extends Yaf_Controller_Abstract
{
    public $actions = array(
        'sample' => 'actions/test/Sample.php',
    );
}
