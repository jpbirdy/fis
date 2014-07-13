<?php
/**
 * @desc 业务类异常，主要是指当业务发生异常时候抛的，用于业务类
 * @author liumingjun@baidu.com
 */

class SF_Exception_BizException extends SF_Exception_AppExceptionBaseSF {
    /**
     * @param string $code
     * @param string $message
     * @param array $errors
     */
    public function __construct($code, $message ='', $errors = array())
    {
        if(empty($message))
        {
            $message = $this->getDisplayErrMsgByCode($code);
        }


        $internalMessage = $this->buildMessage(LOG_ID,$message, $this->getInternalErrMsgByCode($code),$message, $errors);
        $this->_errors = $errors;
        parent::__construct($message, $code);
        SF_Log_Manager::getBDLogger()->warning($internalMessage .'[trace]'.$this->getTraceAsString(), $this->getCodeWithPrefix($code));
    }
}