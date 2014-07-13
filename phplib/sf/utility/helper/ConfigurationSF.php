<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Helper_ConfigurationSF extends SF_Utility_SingleInstanceSF {

    /**
     * @param string $path 配置路径
     * @param string $app 要获取的app的名称
     * @return bool|string
     */
    public function getAppConf($path, $app = 'tradecenter')
    {
        return SF_Wrapper_KSArch_ConfSF::getConf($path, $app);
    }


    public function getCurrApp()
    {
        return SF_Wrapper_KSArch_ConfSF::getCurrApp();

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