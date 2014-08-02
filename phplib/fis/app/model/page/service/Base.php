<?php

/**
 * @desc model层page层基类
 * @author jpbirdy
 */
abstract class Fis_App_Model_Page_Service_Base
{
    private $_param = null;

    /**
     * @param null $param
     */
    function __construct($param = null)
    {
        $this->setInput($param);
    }

    /**
     * @desc PS层请求入口
     * @param array|null $psInput
     * @return array
     */
    function execute($psInput = null)
    {
        try
        {
            if (is_null($psInput))
            {
                $psInput = $this->_param;
            }
            //将数据对象化，如果不支持则不进行对象化
            $this->_call_begin($psInput);
            $psOutput = $this->_call($psInput);
            $this->_call_end($psOutput, $psInput);
            return $psOutput;
        }
        catch (Exception $e)
        {
            return $this->_renderPSResult(array(), $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @desc 组装成PS层应该返回的标准格式
     * @param mixed $psOutput
     * @param int $errno 错误号，默认为0表示成功
     * @param string $msg
     * @return array 此处必须返回array，否则SAF调用会失败。
     */
    public static function _renderPSResult($psOutput, $errno = 0, $msg = '')
    {
        self::_checkNull($psOutput);
        $ret = array(
            'errno' => $errno,
            'errmsg' => $msg,
            'msg' => $msg,
            'data' => new stdClass(),
            'timestamp' => time(),
            'serverstatus' => 0,
            'cached' => 0
        );
        if ($errno == 0 && $psOutput != null)
        {
            $ret['data'] = $psOutput;
        }
        return $ret;
    }

    /**
     * @desc 删除返回值中为空的，直接返回null会造成解析错误
     * @param array $psInput
     * @return null
     */
    public function _checkNull(&$psInput)
    {
        foreach ($psInput as $k => $v)
        {
            if (is_array($v))
            {
                self::_checkNull($psInput[$k]);
            }
            if ($psInput[$k] === null)
            {
                unset($psInput[$k]);
            }
        }
    }

    /**
     * @desc 将PS返回的值调整成适应Action的值
     * @param array $arrRes
     * @return array
     */
    final static public function adpatePSResultForAction($arrRes)
    {
        return $arrRes;
    }

    /**
     * @desc PS的主体实现
     *
     * @param $psInput
     * @return mixed
     */
    abstract protected function _call($psInput);

    /**
     * @desc 在调用_call前执行的方法，可以对psInput进行修改
     * @param $psInput
     */
    protected function _call_begin(&$psInput)
    {

    }

    /**
     * @desc 在调用完_call后执行的方法，可以对psOutput进行修改
     * @param $psOutput
     */
    protected function _call_end(&$psOutput, &$psInput)
    {
        $psOutput = $this->_renderPSResult($psOutput['data'], $psOutput['errno'], $psOutput['errmsg']);
        self::_addLogNotice($psInput);
//        Bd_Log::addNotice('jp' , 'loveyi');
    }

    /**
     * @param $param
     */
    protected function setInput($param)
    {
        $this->_param = $param;
    }

    /**
     * 获取passinfo
     * @return 失败false
     */
    protected function _getUserInfo()
    {
        return Saf_SmartMain::getUserInfo();
    }

    /**
     * 从COOKIE中获取passuid
     * @return mixed
     */
    protected function _getPassUid()
    {
        $userInfo = Saf_SmartMain::getUserInfo();
        $pass_uid = $userInfo['uid'];
        if ($pass_uid > 0)
        {
            self::_checkUserStatus($_COOKIE['BDUSS']);
        }
        return $pass_uid;
    }

    /**
     * 根据BDUSS获取passuid
     * @param $bduss
     * @return mixed
     */
    protected function _getPassUidByBDUSS($bduss)
    {
        return 0;
        /*
        if ($_COOKIE['BDUSS'] == $bduss)
        {
            return 0;
        }
        $userInfo = Bd_Passport::getData($bduss);
        $pass_uid = $userInfo['uid'];
        if ($pass_uid > 0)
        {
            self::_checkUserStatus($bduss);
        }
        return $pass_uid;
        */
    }


    protected function _checkUserStatus($bduss)
    {
        $userinfo = Service_Data_Fis_App_User_Center_Userinfo::getInstance();
        $userinfo->setParams($bduss);
        if (!$userinfo->CheckStatus())
        {
            throw new Fis_App_App_Exception(
                Fis_App_App_Exception::USER_FORBID_MSG,
                Fis_App_App_Exception::USER_FORBID);
        }
        if ($userinfo->CheckisSync())
        {
            throw new Fis_App_App_Exception(
                Fis_App_App_Exception::USER_SYNC_MSG,
                Fis_App_App_Exception::USER_SYNC);
        }
    }

    /**
     * 添加notice信息
     * @param $psInput
     */
    protected function _addLogNotice(&$psInput)
    {
        //字段名修改为ODP默认字段，不需要映射
        $arrLog['product'] = 'baidunuomi';
        $arrLog['subsys'] = 'Fis_App';
        //LOGID用timestamp定位
        $arrLog['log_id'] = isset($psInput['log_id']) ?
                                    $psInput['log_id'] :
                                    (isset($psInput['timestamp']) ? $psInput['timestamp'] : LOG_ID);
        //passuid替代login_id
        $arrLog['login_id'] = isset($psInput['pass_uid']) ? $psInput['pass_uid'] : '';
        //打印s值
        $arrLog['s'] = isset($psInput['s']) ? $psInput['s'] : '';
        //app版本号
        $arrLog['app_version'] = isset($psInput['v']) ? $psInput['v'] : '';
        //终端类型
        $arrLog['terminal_type'] = isset($psInput['terminal_type']) ? $psInput['terminal_type'] : '';
//        $arrLog['device_type'] = isset($psInput['device_type']) ? $psInput['device_type'] : '';
        //设备类型
        $arrLog['device_type'] = isset($psInput['device']) ? $psInput['device'] : '';
        //cuid
        $arrLog['cuid'] = isset($psInput['cuid']) ? $psInput['cuid'] : '';
        //系统版本
        $arrLog['os_version'] = isset($psInput['os']) ? $psInput['os'] : '';
        //lbsidfa
        $arrLog['IDFA'] = isset($psInput['lbsidfa']) ? $psInput['lbsidfa'] : '';
        //UUID
        $arrLog['uuid'] = isset($psInput['uuid']) ? $psInput['uuid'] : '';
        //location
        if(isset($psInput['location']))
        {
            $locations = explode(',' , $psInput['location']);
            $arrLog['lng'] = $locations[0];
            $arrLog['lat'] = $locations[1];
        }
        else
        {
            $arrLog['lat'] = isset($psInput['lat']) ? $psInput['lat'] : '';
            $arrLog['lng'] = isset($psInput['lng']) ? $psInput['lng'] : '';
        }
        //渠道号
        $arrLog['channel'] = isset($psInput['channel']) ? $psInput['channel'] : '';
        //客户端类型
        $arrLog['client_type'] = isset($psInput['client_type']) ? $psInput['client_type'] : '';
        //城市id
        if(isset($psInput['cityid']))
        {
            $city = $psInput['cityid'];
        }
        elseif(isset($psInput['cityId']))
        {
            $city = $psInput['cityId'];
        }
        elseif(isset($psInput['city_id']))
        {
            $city = $psInput['city_id'];
        }
        else
        {
            $city = '';
        }
        $arrLog['target_city'] = $city;
        Saf_Base_Log  :: addLogNotice($arrLog);
    }


}