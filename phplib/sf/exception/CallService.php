<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Exception_CallService extends SF_Exception_InternalException {
    /**
     * @param string $message
     * @param array $errors
     */
    function __construct($message ='', $errors = array())
    {
        parent::__construct(SF_Exception_ErrCodeMapping::CALLSERVICE_ERROR_RETURN, $message, $errors);
    }
} 