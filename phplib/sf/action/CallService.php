<?php

/**
 * @desc 外层实现名字必须是Action_Service
 * @author liumingjun@baidu.com
 */

abstract class SF_Action_CallService extends SF_Action_ApiBase {

    abstract protected function _enableHttp();

    /**
     * 具体执行逻辑
     * @return mixed
     */
    public function __execute(){
        if ($this->_enableHttp())
        {
            $postData = $this->getPostData();
            $dispatcher = new SF_CallService_Dispatcher();
            $output = $dispatcher->execute($postData);
            //$output = $output['arrRes'];
            return $output;
        }
        else
        {
            return array('not Allow Http');
        }
    }
}