<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Log_LogEngine_BDLogEngine implements SF_Interface_ILog {

    /**
     * @var int
     */
    private $_depth = 1;

    /**
     * @desc 获取预取深度
     * @param int $depth
     * @return int
     */
    private function _getDepth($depth)
    {
        if (!is_null($depth) || !is_numeric($depth))
        {
            return $this->_depth;
        }
        return $depth;
    }
    
    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     */
    function fatal($message, $code = 0, $errors = array(), $depth =NULL)
    {

        $depth = $this->_getDepth($depth);
        Bd_Log::fatal($message, $code, $errors, $depth);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     */
    function notice($message, $code = 0, $errors = array(), $depth =NULL)
    {
        $depth = $this->_getDepth($depth);

        Bd_Log::notice($message, $code, $errors, $depth);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     */
    function warning($message, $code = 0, $errors = array(), $depth =NULL)
    {
        $depth = $this->_getDepth($depth);

        Bd_Log::warning($message, $code, $errors, $depth);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     */
    function trace($message, $code = 0, $errors = array(), $depth =NULL)
    {
        $depth = $this->_getDepth($depth);

        Bd_Log::trace($message, $code, $errors, $depth);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    function addNotice($key, $value)
    {

        Bd_Log::addNotice($key,$value);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $errors
     * @param int $depth
     */
    function debug($message, $code = 0, $errors = array(), $depth =NULL)
    {
        $depth = $this->_getDepth($depth);
        Bd_Log::debug($message, $code, $errors, $depth);
    }

    /**
     * @desc 增加深度，用以回溯trace
     * @param int $depth
     * @return void
     */
    function addDepth($depth)
    {
        $this->_depth = $this->_depth + $depth;
    }
}