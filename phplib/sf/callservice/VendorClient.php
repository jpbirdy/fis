<?php
/**
 * @desc 请在自己的app中继承实现，并遵照单例模式实现
 *
 * <code>
 *   #region 实现样例
 *   class Api_CallServiceClient extends VendorClient{
 *
 *
 *   protected function _getToken()
 *   {
 *       return '12312314';
 *   }
 *
 *
 *   static public function callTradeCenterRefund($op, $data, $responseModel = null)
 *   {
 *       $strUrlPathInfo = '/tradecenter/refund';
 *       $reqAppName = 'tradecenter';
 *       $httpMethod = 'post';
 *
 *       return self::_callApp(__CLASS__,$op, $data, $responseModel, $reqAppName, $strUrlPathInfo, $httpMethod);
 *   }
 * }
 * </code>
 *
 * @author liumingjun@baidu.com
 */

abstract class VendorClient
{

    const SYS_ERR_OK = 0;

    const REQUEST_PARAM_OP = 'op';
    const REQUEST_PARAM_TOKEN = 'token';
    const REQUEST_PARAM_DATA = 'data';

    #region 私有方法
    private static $_client = array();
    const ERR_CODE = 'errCode';

    /**
     * @desc 返回一个SF_CallService_Client的实例，自己判断是否要按照单例实现
     * @param string $className 要构造的类名，构造函数需要无参数
     * @return VendorClient
     */
    protected static function _getClient($className)
    {
        if (empty(self::$_client) || !isset(self::$_client[$className]))
        {
            self::$_client[$className] = new $className();
        }
        return self::$_client[$className];
    }
    #endregion


    /**
     * @desc 返回对应app所分配的token值
     * @return string
     */
    abstract protected function _getToken();

    /**
     * @brief 统一调用接口
     *
     * @param string $op 调用的命令，在Action_Service中配置的那些
     * @param array|string $data 提供给真正PS的参数 json格式
     * @param string $reqAppName 提供请求接口的app名称
     * @param string $strUrlPathInfo 当Action调用时使用，为/app/controller，此时走ral
     * @param string $httpMethod
     * @param array|null $arrFilter
     * @param array|null $extra
     * @throws Exception
     * @return array
     */
    protected function call($op, $data, $reqAppName, $strUrlPathInfo, $httpMethod = 'post', $arrFilter = null, $extra = null)
    {

        $strUrlMethod = 'service';
        $strUrlPathInfo = rtrim($strUrlPathInfo);
        $strUrl = $strUrlPathInfo . DIRECTORY_SEPARATOR . $strUrlMethod;

        if (is_array($data)) {
            $data = json_encode($data);
        }

        /**
         * @var $strService 表示saf使用的那个库中
         */
        $strService = 'CallService';
        $strMethod = 'callService';

        $arrParams = $this->_genRequestData($op, $data, $reqAppName, $httpMethod, $strUrl);

        $arrRet = Saf_Api_Server::call($strService, $strMethod, $arrParams, $arrFilter, $extra);

        if ($arrRet === false
            || !isset($arrRet['arrRes'][self::ERR_CODE])
        ) {
            $msg = 'backend work out by call service client [backMsg]';
            throw new Exception($msg.var_export(Saf_Api_Server::getLastError(),true), -1);
        }

        $retData = $arrRet['arrRes'];
        //调整数据结构
        $retData = $this->adaptPSResult($retData);
        return $retData;
    }

    /**
     * @param string $className 传入当前类的类名
     * @param string $op 传入操作名称
     * @param $data
     * @param $reqAppName
     * @param $strUrlPathInfo
     * @param $httpMethod
     * @throws SF_Exception_InternalException
     * @return array|SF_Entity_Collection
     */
    protected static function _callApp($className, $op, $data, $reqAppName, $strUrlPathInfo, $httpMethod)
    {
        $ret = self::_getClient($className)->call($op, $data, $reqAppName, $strUrlPathInfo, $httpMethod);
        return $ret;
    }

    /**
     * @param $op
     * @param $data
     * @param $reqAppName
     * @param $httpMethod
     * @param $strUrl
     * @return array
     */
    protected function _genRequestData($op, $data, $reqAppName, $httpMethod, $strUrl)
    {

        if (is_array($data)|| is_object($data))
        {
            $data = json_encode($data);
        }

        return array(
            'arrReq' => array(
                self::REQUEST_PARAM_OP => $op,
                self::REQUEST_PARAM_TOKEN => $this->_getToken(),
                self::REQUEST_PARAM_DATA => $data,
            ),
            'appName' => $reqAppName,
            'httpMethod' => $httpMethod,
            'strUrl' => $strUrl
        );
    }

    /**
     * @param $arrRes
     * @return array
     */
    protected function adaptPSResult($arrRes)
    {
        if (isset($arrRes[self::ERR_CODE]))
        {
            $newArrRes['errno'] = $arrRes[self::ERR_CODE];
            unset($arrRes[self::ERR_CODE]);
            return array_merge($newArrRes,$arrRes);
        }
        return $arrRes;
    }
}