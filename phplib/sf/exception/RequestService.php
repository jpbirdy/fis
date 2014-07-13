<?php
/**
 * @desc 用于抛出请求服务的异常
 * @author liumingjun@baidu.com
 */

class SF_Exception_RequestService extends SF_Exception_InternalException  {
    /**
     * @desc 用于抛出请求服务的异常
     *
     * @param string $message
     * @param array $errors
     * @param null $previous
     */
    public function __construct($message = '', $errors = array(), $previous = null)
    {
        parent::__construct(SF_Exception_ErrCodeMapping::UTILITY_HELPER_RUQUEST_SERVICE_ERROR, $message, $errors, $previous);
    }
} 