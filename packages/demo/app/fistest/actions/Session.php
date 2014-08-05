<?php

/**
 * @name Action_Session
 * @desc 一个样例Action，包括页面返回、逻辑action跳转、json数据返回
 */
class Action_Session extends Fis_App_Action_Base
{

    //execute返回，建议用const常量指定
    //Action_Base里已经指定的有
    //SUCCESS ERROR INDEX LOGIN LOGOUT


    //定义页面返回类型
    /**
     * type : page为返回一个渲染页，json为返回一个json串，action，表示进行业务跳转
     * @var array
     */
    protected $_result_mapping = array(
        self::SUCCESS => array('result' => 'index', 'type' => Fis_App_Action_Base::RESULT_TYPE_PAGE),
    );

    protected $_result_data = array();

    /**
     * @desc 业务层执行部分，最后结果要return给execute
     * @return string
     */
    protected function __execute()
    {
        self::_setResultData(array('content' => 'jpbirdy'));
        $session = self::_getSession();
        $session->setSession('user' , 'jpbirdy');
        echo 'in redis session:' . ($session->getSession('user'));
        return self::SUCCESS;
    }
}
