<?php
/**
 * @name account_Controller
 * @desc 主控制器,也是默认控制器
 */
class Controller_Api extends Yaf_Controller_Abstract {
	public $actions = array
    (
		'sample' => 'actions/Sample.php',
        'forward' => 'actions/Forward.php',
	);
}
