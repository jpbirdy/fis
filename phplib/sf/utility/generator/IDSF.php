<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Generator_IDSF extends SF_Utility_SingleInstanceSF {

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
    static function getClass()
    {
        return __CLASS__;
    }


}