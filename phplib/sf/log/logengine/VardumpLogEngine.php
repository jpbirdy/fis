<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Log_LogEngine_VardumpLogEngine implements SF_Interface_ILog {

    const FATAL = '[FATAL]';
    const NOTICE = '[NOTICE]';
    CONST WARNING = '[WARNING]';
    CONST TRACE = '[TRACE]';
    CONST DEBUG = '[DEBUG]';

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function fatal($message, $code = 0, $errors = array(), $depth = 1)
    {
        $this->printLog($message,$code,$errors,self::FATAL);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function notice($message, $code = 0, $errors = array(), $depth = 1)
    {
        $this->printLog($message,$code,$errors,self::NOTICE);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function warning($message, $code = 0, $errors = array(), $depth = 1)
    {
        $this->printLog($message,$code,$errors,self::WARNING);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function trace($message, $code = 0, $errors = array(), $depth = 1)
    {
        $this->printLog($message,$code,$errors,self::TRACE);
    }

    /**
     * @param $message
     * @param $code
     * @param $errors
     * @param string $type
     */
    function printLog($message,$code, $errors, $type = self::TRACE)
    {
        var_dump('BEGIN-----------------------'.$type.'------------------------------');

        var_dump('[NOTICE]',$this->_addNotice,'[code]'.$code,'[message]'.$message);
        var_dump('END-----------------------'.$type.'------------------------------');

    }

    private $_addNotice = array();

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function addNotice($key, $value)
    {
        $this->_addNotice[$key] = $value;
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     * @return void
     */
    function debug($message, $code = 0, $errors = array(), $depth = 1)
    {
        $this->printLog($message,$code,$errors,self::DEBUG);
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