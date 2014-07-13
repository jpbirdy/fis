<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Exception_ExceptionBase extends Exception implements SF_Interface_ISupplyErrMsgCode {

    /**
     * @desc 提供错误码的类
     * @return SF_Interface_ISupplyErrMsgCode
     */
    abstract protected function _getSupplyErrMsgCodeEngine();

    /**
     * @param string $message
     * @param int $code
     */
    function __construct($message = "",$code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getDisplayErrMsgByCode($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getDisplayErrMsgByCode($code);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getInternalErrMsgByCode($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getInternalErrMsgByCode($code);
    }

    /**
     * @desc 返回自动拼贴前缀的错误号
     *
     * @param int $code
     * @return int
     */
    function getCodeWithPrefix($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getCodeWithPrefix($code);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getDisplayCode($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getDisplayCode($code);
    }
}