<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Api_CallService_Entity_ReqcallService extends Saf_Api_Entity{
    public $arrReq = null;
    public $httpMethod = 'post';
    public $strUrl = null;
    public $appName = null;

    /**
     * @desc 通过该函数获得，可以供saf自动构造出Entity的数组
     *
     * @param $arrReq
     * @param $appName
     * @param string $httpMethod
     * @param null $strUrl
     * @return array
     */
    static function genReqData($arrReq, $appName, $httpMethod = 'post', $strUrl = null)
    {
        return array(
            'arrReq' => $arrReq,
            'appName' => $appName,
            'httpMethod' => $httpMethod,
            'strUrl' => $strUrl
        );
    }
}