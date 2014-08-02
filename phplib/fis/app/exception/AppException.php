<?php
/**
 * @desc APP异常类，在业务层外执行的代码会抛出该异常
 * @author jpbirdy
 */

class Fis_App_Exception_AppException extends Fis_App_Exception_ExceptionBase
{
    /**
     * @abstract
     * @desc 提供错误码的类，如果要更换不同的引擎，需要覆写该方法
     * @return Fis_App_Exception_ErrCodeMapping
     */
    protected function _getSupplyErrMsgCodeEngine()
    {
        return new Fis_App_Exception_ErrCodeMapping();
    }

    /**
     * @param string $message
     * @param int $code
     * @throws Fis_App_Exception_AppException
     */
    function __construct($message = '', $code = 0)
    {
        $code = $this->_getSupplyErrMsgCodeEngine()->getDisplayCode($code);
        parent::__construct($this->_getSupplyErrMsgCodeEngine()->getDisplayErrMsgByCode($code , $message), $code);
    }


    /**
     * @param int $logId
     * @param string $displayMessage 展示给外部的错误信息
     * @param string $interanlMessage 内部的错误信息
     * @param string $selfMessage   定制的错误信息
     * @return string
     */
    protected function buildMessage($logId, $displayMessage,$interanlMessage)
    {
        return '[logID]' . $logId . '[displayMessage]' . $displayMessage. '[interanlMessage]' . $interanlMessage;
    }


}