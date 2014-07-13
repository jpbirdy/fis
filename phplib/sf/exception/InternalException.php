<?php
/**
 * @desc 用于基础函数的请求参数错误或后端服务（数据库等）异常
 * @author liumingjun@baidu.com
 */

class SF_Exception_InternalException extends SF_Exception_AppExceptionBaseSF {

    /**
     * @param int $code
     * @param string $message
     * @param array $errors
     * @param null $previous
     */
    public function __construct($code, $message = '', $errors = array(), $previous = null)
    {
        if(empty($message))
        {
            $message = $this->getInternalErrMsgByCode($code);
        }
        $errorsMsg = '';
        if(!empty($errors))
        {
            $this->_errors = $errors;
        }
        $newMessage = $this->buildMessage(LOG_ID,$this->getDisplayErrMsgByCode($code),$message, $message, $errors);
        parent::__construct($message, $code);
        SF_Log_Manager::getBDLogger()->fatal($newMessage.$errorsMsg.'[trace]'.$this->getTraceAsString(), $this->getCodeWithPrefix($code));
    }
}