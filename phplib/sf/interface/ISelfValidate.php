<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ISelfValidate {


    /**
     * @desc 用于对该类进行必要、通用的自我验证，切勿融入与业务有关的验证
     * @return mixed
     */
    function validate();
} 