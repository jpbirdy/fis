<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Action_ApiBase extends SF_Action_Base {

    /**
     * @desc 当只是需要返回值时实现
     * @param $arrRes
     */
    protected function _value($arrRes){
        echo json_encode($arrRes);
    }

    /**
     * @desc 判断合适会渲染页面，合适只是返回值
     * @return bool
     */
    function _isRenderView()
    {
        return false;
    }
}