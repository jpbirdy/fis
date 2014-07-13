<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Log_LogEngine_SelfLogEngine implements SF_Interface_ILog {

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     */
    function fatal($message, $code = 0, $errors = array(), $depth = 1)
    {
        // TODO: Implement fatal() method.
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     */
    function notice($message, $code = 0, $errors = array(), $depth = 1)
    {
        // TODO: Implement notice() method.
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     */
    function warning($message, $code = 0, $errors = array(), $depth = 1)
    {
        // TODO: Implement warning() method.
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     */
    function trace($message, $code = 0, $errors = array(), $depth = 1)
    {
        // TODO: Implement trace() method.
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function addNotice($key, $value)
    {
        // TODO: Implement addNotice() method.
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @return void
     */
    function debug($message, $code = 0, $errors = array(), $depth = 1)
    {
        // TODO: Implement debug() method.
    }

    /**
     * @desc 增加深度，用以回溯trace
     * @param int $depth
     * @return void
     */
    function addDepth($depth)
    {
        // TODO: Implement addDepth() method.
    }
}