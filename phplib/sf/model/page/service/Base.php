<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Model_Page_Service_Base implements SF_Interface_IGetRequestModelName {
//    const ERR_CODE = 'errCode';
    const ERR_CODE = 'errno';


    /**
     * @var array 用于控制输出的字段，空时，表示不裁剪
     */
    protected $_withConfig = array();
    /**
     * @var array
     */
    private $_afArr;

    private $_param = null;

    /**
     * @desc 用于判断是否返回某个节点，用于定制返回用的
     * @param string $node 节点名
     * @return bool
     */
    protected function _isNeedNode($node)
    {
        return in_array($node, $this->_withConfig) || empty($this->_withConfig);
    }


    /**
     * @param array $arrNodes array('order', 'coupon')之类的
     */
    protected function _setMustNeedNode($arrNodes)
    {
        if (!empty($this->_withConfig))
        {
            $this->_withConfig = array_merge($this->_withConfig, $arrNodes);
        }
    }

    /**
     * @param null $param
     */
    function __construct($param = null)
    {
        $this->setInput($param);
    }

    /**
     * @desc PS层请求入口，不可重写
     * @param array|null $psInput
     * @return mixed
     */
    function execute($psInput = null)
    {
        try
        {
            if (is_null($psInput))
            {
                $psInput = $this->_param;
            }

            if (!is_null($psInput) && !empty($psInput) && isset($psInput['with']))
            {

                $with = json_decode(isset($psInput['with']), true);

                if (!is_null($with) && $with !== false)
                {
                    $this->_withConfig = $psInput['with'];
                }
            }
            //将数据对象化，如果不支持则不进行对象化
            $psInput = $this->objectlize($psInput,$this);
            $this->runAspectFunciton($psInput, null);
            $this->_call_begin($psInput);

            $psOutput = $this->_call($psInput);

            $this->_call_end($psOutput);
            $this->runAspectFunciton($psInput, $psOutput, false);
            return $psOutput;
        }
        catch(Exception $e)
        {
            return $this->_renderPSResult(array(), $e->getCode(),$e->getMessage());
        }
    }

    /**
     * @desc 组装成PS层应该返回的标准格式，这里之所以不用errno是因为，会为了避免被saf拦截
     * @param mixed $psOutput
     * @param int $errno 错误号，默认为0表示成功
     * @param string $msg
     * @return array
     */
    final protected function _renderPSResult($psOutput, $errno = SF_Exception_ErrCodeMapping::SYSTEM_OK, $msg = '' )
    {
        if (is_null($errno) || $errno === '')
        {
            $errno = SF_Exception_ErrCodeMapping::SYSTEM_OK;
        }

        if (!empty($this->_withConfig))
        {
            $ret = array();
            if (!empty($this->_withConfig))
            {
                foreach ($this->_withConfig as $field) {
                    if (array_key_exists($field, $psOutput))
                    {
                        $ret[$field] = $psOutput[$field];
                    }
                    else
                    {
                        $ret[$field] = null;
                    }
                }

            }
            $psOutput = &$ret;
        }

        return array(
            self::ERR_CODE => $errno,
            'msg' => $msg,
            'data' => $psOutput,
        );
    }

    /**
     * @desc 将PS返回的值调整成适应Action的值
     * @param array $arrRes
     * @return array
     */
    final static public function adpatePSResultForAction($arrRes)
    {
        if (isset($arrRes[self::ERR_CODE]))
        {
            $newArrRes['errno'] = $arrRes[self::ERR_CODE];
            unset($arrRes[self::ERR_CODE]);
            return array_merge($newArrRes,$arrRes);
        }
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
    protected function _call_begin(&$psInput){

    }

    /**
     * @desc 在调用完_call后执行的方法，可以对psOutput进行修改
     * @param $psOutput
     */
    protected function _call_end(&$psOutput){

    }

    /**
     * @desc 运行所有切面功能
     * @param $psInput 进入ps的输入参数
     * @param $psOutput 输出的ps层运行结果
     * @param bool $isBegin
     */
    private function runAspectFunciton($psInput, $psOutput, $isBegin = true)
    {
        if (empty($this->_afArr))
        {
            return;
        }
        /**
         * @var SF_Interface_IAspectFunc $af
         */
        foreach($this->_afArr as $af)
        {
            if ($isBegin)
            {
                $af->begin($psInput);

            }
            else
            {
                $af->end($psInput,$psOutput);
            }
        }
    }

    /**
     * @desc 设置切面方法集合
     * @param array $afArr array('切面方法的类名')
     * @throws SF_Exception_InternalException
     */
    final protected function setAspectFunctionCollection($afArr)
    {
        if (!empty($afArr))
        {
            /**
             * @var SF_Interface_IAspectFunc $af
             */
            foreach($afArr as $af)
            {
                if (!($afArr instanceof SF_Interface_IAspectFunc))
                {
                    throw new Naserver_Exception_InternalException(SF_Exception_ErrCodeMapping::ASPECT_FUNC_DEFINE_ERROR,'传入了错误的切面方法类，请实现IAspectFunc接口['.$af.']');
                }
            }

            $this->_afArr = $afArr;
        }
    }

    /**
     * @desc 注册启用的切面方法，当需要启用不同的切面功能时，可以重写
     *
     */
    protected function registerAspectFunction()
    {
        $this->setAspectFunctionCollection(array());
    }


    /**
     * @param array $arrData
     * @param SF_Model_Page_Service_Base $pageServiceObj
     * @throws SF_Exception_InternalException
     * @return SF_Entity_RequestModel
     */
    protected function objectlize($arrData, $pageServiceObj)
    {
        if ($pageServiceObj instanceof SF_Interface_IGetRequestModelName)
        {
            $requestModelClassName = $pageServiceObj->getReqModelClassName();
            if (empty($requestModelClassName))
            {
                return $arrData;
            }
            else
            {
                if (!class_exists($requestModelClassName))
                {
                    throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_OBJECTLIZE_ERROR);
                }
                else
                {
                    $reqModel = new $requestModelClassName($arrData);
                    return $reqModel;
                }
            }
        }

        return $arrData;
    }

    /**
     * @return SF_Interface_ILog
     */
    protected function logger()
    {
        return SF_Log_Manager::getBDLogger(SF_Log_Manager::LOGGER_DEPTH);
    }

    /**
     * @param $param
     */
    protected function setInput($param)
    {
        $this->_param = $param;
    }

    protected function _getUserInfo()
    {
        return Saf_SmartMain::getUserInfo();
    }

    protected function _getPassUid()
    {
        $userInfo = Saf_SmartMain::getUserInfo();
        return $userInfo['uid'];
    }
}