<?php
/**
 * @desc 请在自己的app中继承实现，并遵照单例模式实现
 *
 * <code>
 *   #region 推荐单例写法
 *   private static $_client = null;
 *   private static function _getClient()
 *   {
 *       if (self::$_client === null)
 *      {
 *           self::$_client = new Api_CallServiceClient();
 *       }
 *       return self::$_client;
 *   }
 *   #endregion
 * </code>
 *
 * @author liumingjun@baidu.com
 */

abstract class SF_CallService_Client
{

    const SYS_ERR_OK = SF_Exception_ErrCodeMapping::SYSTEM_OK;

    #region 私有方法
    private static $_client = array();

    /**
     * @desc 返回一个SF_CallService_Client的实例，自己判断是否要按照单例实现
     * @param string $className 要构造的类名，构造函数需要无参数
     * @return SF_CallService_Client
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
     * @throws SF_Exception_CallService
     * @internal param $token 分配的token，用来做权限控制，性能控制等。
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

        $callServiceReqModel = new SF_Entity_CallServiceRequestModel();
        $callServiceReqModel->instantiate(array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_OP => $op,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN => $this->_getToken(),
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_DATA => $data,
        ));

        $callServiceReqModel->data = json_encode($callServiceReqModel->data);
        $arrParams = SF_Api_CallService_Entity_ReqcallService::genReqData($callServiceReqModel->transToArr(),$reqAppName, $httpMethod, $strUrl);

        $arrRet = Saf_Api_Server::call($strService, $strMethod, $arrParams, $arrFilter, $extra);


        if ($arrRet === false
            || !isset($arrRet['arrRes'][SF_Model_Page_Service_Base::ERR_CODE])
        ) {
            $msg = 'backend work out by call service client [backMsg]';
            throw new SF_Exception_CallService($msg, array(
                'op' => $op,
                'data' => $data,
                'token' => $this->_getToken(),
                'strUrlPathInfo' => $strUrlPathInfo,
                'httpMethod' => $httpMethod,
                'safLastError' => Saf_Api_Server::getLastError()
            ));
        }

        $retData = $arrRet['arrRes'];

        //调整数据结构
        $retData = SF_Model_Page_Service_Base::adpatePSResultForAction($retData);
        return $retData;
    }

    /**
     * @param string $className 传入当前类的类名
     * @param string $op 传入操作名称
     * @param $data
     * @param $responseModel
     * @param $reqAppName
     * @param $strUrlPathInfo
     * @param $httpMethod
     * @throws SF_Exception_InternalException
     * @return array|SF_Entity_Collection
     */
    protected static function _callApp($className, $op, $data, $responseModel, $reqAppName, $strUrlPathInfo, $httpMethod)
    {
        if (!is_null($responseModel) && !$responseModel instanceof SF_Entity_ResponseModel) {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::CALLSERVICE_ABSENT_MODEL,'传入了错误的ResponseModel',array(
                'responseModel' => var_export($responseModel,true)
            ));
        }
        $ret = self::_getClient($className)->call($op, $data, $reqAppName, $strUrlPathInfo, $httpMethod);
        if (!is_null($responseModel)) {
            if (SF_Utility_Manager::validator($ret, 'call service return data', false)->isIndexArray()) {
                $collection = new SF_Entity_Collection();
                foreach ($ret as $entry) {
                    $responseModel->instantiate($entry);
                    $collection->append($responseModel);

                }
                return $collection;
            }
            else {
                $responseModel->instantiate($ret);
                return $responseModel;
            }

        }
        else {
            return $ret;
        }
    }
}