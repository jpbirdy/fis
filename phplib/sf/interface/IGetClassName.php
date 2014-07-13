<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_IGetClassName {


    /**
     * @desc 返回当前类的类名
     *
     * 内部代码如下
     * <code>
     *  return __CLASS__;
     * </code>
     *
     * @return string
     */
    static function getClass();

} 