<?php

/**
 * @name Action_Call
 * @desc sample action, 和url对应
 */
class Action_Call extends Fis_App_Action_Base
{
    //定义页面返回类型
    /**
     * type : page为返回一个渲染页，json为返回一个json串，action，表示进行业务跳转
     * @var array
     */
    protected  $_result_mapping =
    array
    (
        'success' => array('result' => 'index' , 'type' => Fis_App_Action_Base::RESULT_TYPE_PAGE),
    );

    protected $_result_data = array();

    /**
     * @desc 业务层执行部分，最后结果要return给execute
     * @return array
     */
    protected function __execute()
    {
        self::_setResultData(array('content' => 'jpbirdy'));
        return 'success';
    }

}
