<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Helper_RequestServiceSF extends SF_Utility_SingleInstanceSF {

    private static $_requestServiceContainer = array();

    const HTTP_METHOD_GET = SF_Wrapper_KSArch_RalSF::RAL_GET;
    const HTTP_METHOD_POST = SF_Wrapper_KSArch_RalSF::RAL_POST;

    const SERVICE_RAL = 'ral';
    const SERVICE_CURL = 'curl';

    /**
     * @desc 返回ral的实例
     *
     * @return SF_Wrapper_KSArch_RalSF
     */
    public function Ral()
    {
        if (!array_key_exists(self::SERVICE_RAL, self::$_requestServiceContainer))
        {
            self::$_requestServiceContainer[self::SERVICE_RAL] = new SF_Wrapper_KSArch_RalSF();

        }
        return self::$_requestServiceContainer[self::SERVICE_RAL];

    }


    /**
     * @desc 返回curl的实例
     *
     * @return SF_Wrapper_CurlSF
     */
    public function Curl()
    {
        if (!array_key_exists(self::SERVICE_CURL, self::$_requestServiceContainer))
        {
            self::$_requestServiceContainer[self::SERVICE_CURL] = new SF_Wrapper_CurlSF();

        }
        return self::$_requestServiceContainer[self::SERVICE_CURL];
    }

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