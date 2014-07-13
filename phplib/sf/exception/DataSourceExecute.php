<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Exception_DataSourceExecute extends SF_Exception_InternalException {
    /**
     * @param string $message
     * @param array $errors
     */
    function __construct($message ='', $errors = array())
    {
        parent::__construct(SF_Exception_ErrCodeMapping::DATASOURCE_EXECUTE_ERROR, $message, $errors);
    }
}