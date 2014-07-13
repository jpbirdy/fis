<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ICacheOperate {

    /**
     * @param string $key
     * @return string
     */
    function get($key);

    /**
     * @param string $key
     * @param mixed $val
     * @return void
     */
    function set($key, $val);

} 