<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ILog {


    /**
     * @desc 增加深度，用以回溯trace
     * @param int $depth
     * @return void
     */
    function addDepth($depth);

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function addNotice($key, $value);

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function fatal($message, $code = 0, $errors= array(), $depth = 0);

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @return void
     */
    function notice($message, $code = 0, $errors= array());

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function warning($message, $code = 0, $errors= array(), $depth = 0);

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function trace($message, $code = 0, $errors= array(), $depth = 0);

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function debug($message, $code = 0, $errors= array(), $depth = 0);
} 