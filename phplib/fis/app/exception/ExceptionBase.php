<?php
/**
 * @desc 异常基类，在异常基类中会打印warning日志
 * @author jpbirdy
 */

abstract class Fis_App_Exception_ExceptionBase extends Exception
{
    /**
     * @desc 错误码引擎
     */
    abstract protected function _getSupplyErrMsgCodeEngine();

    /**
     * @param string $message
     * @param int $code
     */
    function __construct($message = '',$code = 0)
    {
        parent::__construct($message, $code);

    }

    /**
     * @param int $code
     * @return string
     */
    public function _getDisplayErrMsgByCode($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getDisplayErrMsgByCode($code);
    }

    /**
     * @param int $code
     * @return string
     */
    public function _getInternalErrMsgByCode($code)
    {
        return $this->_getSupplyErrMsgCodeEngine()->getInternalErrMsgByCode($code);
    }


    /**
     * @param int $logId
     * @param string $displayMessage 错误信息
     * @param int $displayCode 内部的错误码
     * @return string
     */
    protected function _buildMessage($logId, $displayMessage , $displayCode)
    {
        return '[logID]' . $logId . '[displayMessage]' . $displayMessage. '[$displayCode]' . $displayCode;
    }


}