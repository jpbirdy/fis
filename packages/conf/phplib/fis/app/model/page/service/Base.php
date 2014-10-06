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
        $this->_setInput($param);
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
            $this->_callBegin($psInput);
            $psOutput = $this->_call($psInput);
            $this->_callEnd($psOutput, $psInput);
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
    public static function _renderPSResult($psOutput, $errno = 0, $msg = '' )
    {
        self::_checkNull($psOutput);
        $ret = array(
            'errno' => $errno,
            'msg' => $msg,
            'data' => new stdClass(),
            'timestamp' => time(),
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
    protected function _callBegin(&$psInput)
    {

    }

    /**
     * @desc 在调用完_call后执行的方法，可以对psOutput进行修改
     * @param $psOutput
     */
    protected function _callEnd(&$psOutput, &$psInput)
    {
        $psOutput = $this->_renderPSResult($psOutput['data'], $psOutput['errno'], $psOutput['errmsg']);
//        Bd_Log::addNotice('jp' , 'loveyi');
    }

    /**
     * @param $param
     */
    protected function _setInput($param)
    {
        $this->_param = $param;
    }


}