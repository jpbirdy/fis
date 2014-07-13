<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_IGetRequestModelName {
    /**
     * @desc 返回空字符串时，表示不使用requestModel。获取接口对应的RequestModel的类名，当需要对象化时需要重写，返回正确的类.
     * @return string
     */
    public function getReqModelClassName();
} 