<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ISupplyErrMsgCode {
    /**
     * @param int $code
     * @return string
     */
    function getDisplayErrMsgByCode($code);

    /**
     * @param int $code
     * @return string
     */
    function getInternalErrMsgByCode($code);


    /**
     * @desc 返回自动拼贴前缀的错误号
     *
     * @param int $code
     * @return int
     */
    function getCodeWithPrefix($code);

    /**
     * @desc 返回对外显示的错误号
     *
     * @param int $code
     * @return int
     */
    function getDisplayCode($code);
}