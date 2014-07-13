<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Api_CallService_Interface {

    /**
     * @desc 通过的callService入口方法
     * @param SF_Api_CallService_Entity_ReqcallService $req
     * @param SF_Api_CallService_Entity_RescallService $res
     * @return mixed
     */
    public function callService(SF_Api_CallService_Entity_ReqcallService $req, SF_Api_CallService_Entity_RescallService$res);


} 