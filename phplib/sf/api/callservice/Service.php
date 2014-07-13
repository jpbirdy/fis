<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Api_CallService_Service extends Saf_Api_Service implements SF_Api_CallService_Interface {

    /**
     * @desc 通过的callService入口方法
     * @param SF_Api_CallService_Entity_ReqcallService $req
     * @param SF_Api_CallService_Entity_RescallService $res
     * @return mixed
     */
    public function callService(SF_Api_CallService_Entity_ReqcallService $req, SF_Api_CallService_Entity_RescallService $res)
    {
        $this->setAppService($req->appName);
        $arrInput = $req->toArray();
        if ($this->isLocalService())
        {
            $strPageService = SF_CallService_Dispatcher::getClass();
            $strUrl = null;
        }
        else
        {
            $strPageService = null;
            $strUrl = $req->strUrl;
        }
        $arrOutput = null;
        $arrRes = $this->execute($arrInput['arrReq'], $arrOutput, $strPageService, $strUrl, $req->httpMethod);
        $ret = $this->_result(array('arrRes' => $arrRes), $res);
        return $ret;
    }

    /**
     * @desc 将结果转换成SF_Api_CallService_Entity_RescallService对象
     * @param mixed $arrRes
     * @param SF_Api_CallService_Entity_RescallService $res
     * @return bool|null|SF_Api_CallService_Entity_RescallService
     */
    protected function _result($arrRes, SF_Api_CallService_Entity_RescallService $res){
        if($arrRes !== false)
        {
            $res->loadFromArray($arrRes);
            if($res !== false){
                return $res;
            }else{
                return null;
            }
        }
        return false;
    }


    /**
     * @desc 此处相相当于Saf的construct方法
     * @param string $appName 设置请求的appService的相关参数
     */
    protected function setAppService($appName = MAIN_APP)
    {
        parent::__construct($appName);
        $this->oe = 'utf-8'; //命名方式同saf
    }
}