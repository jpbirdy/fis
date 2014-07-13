<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Exception_AppExceptionBaseSF extends SF_Exception_ExceptionBase
{
    /**
     * @abstract
     * @desc 提供错误码的类，如果要更换不同的引擎，需要覆写该方法
     * @return SF_Interface_ISupplyErrMsgCode
     */
    protected function _getSupplyErrMsgCodeEngine()
    {
        return new SF_Exception_ErrCodeMapping();
    }

    /**
     * @param string $message
     * @param int $code
     * @throws SF_Exception_AppExceptionBaseSF
     */
    function __construct($message = '', $code = 0)
    {
        $this->_checkSupplyErrMsgCodeEngine();
        parent::__construct($message, $this->_getSupplyErrMsgCodeEngine()->getDisplayCode($code));
    }

    protected function _checkSupplyErrMsgCodeEngine()
    {
        if (!$this->_getSupplyErrMsgCodeEngine() instanceof SF_Interface_ISupplyErrMsgCode) {
            throw new SF_Exception_AppExceptionBaseSF('', SF_Exception_ErrCodeMapping::EXCEPTION_ERROR_MAPPING_WRONG);
        }
    }

    /**
     * @param int $logId
     * @param string $displayMessage 展示给外部的错误信息
     * @param string $interanlMessage 内部的错误信息
     * @param string $selfMessage   定制的错误信息
     * @param array $variable
     * @return string
     */
    protected function buildMessage($logId, $displayMessage,$interanlMessage, $selfMessage = '' ,$variable = array())
    {
        $varStr = '';
        if (!empty($variable)) {
            $varStr = '[variable]' . var_export($variable, true);
        }
        return '[logID]' . $logId . '[displayMessage]' . $displayMessage. '[interanlMessage]' . $interanlMessage. '[selfMessage]' . $selfMessage . $varStr;
    }


}