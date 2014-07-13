<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_IAspectFunc {

    /**
     * @desc 切面处理程序开始部分
     * @param $psInput
     *
     * @return void
     */
    function begin($psInput);

    /**
     * @desc  切面处理程序结束部分
     * @param $psInput
     * @param $psOutput
     *
     * @return void
     */
    function end($psInput, $psOutput);
} 