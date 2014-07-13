<?php
/**
 * @desc 用于抛出异步提交类的异常
 * @author liumingjun@baidu.com
 */

class SF_Exception_AsyncSubmit extends SF_Exception_InternalException {
    /**
     * @desc 用于抛出异步提交类的异常
     *
     * @param string $message
     * @param array $errors
     * @param null $previous
     */
    public function __construct($message = '', $errors = array(), $previous = null)
    {
        parent::__construct(SF_Exception_ErrCodeMapping::UTILITY_HELPER_ASYNC_SUBMIT_ERROR, $message, $errors, $previous);
    }

} 