<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_IValidate {
    /**
     * @param mixed $value
     * @return SF_Utility_Validate_ValidateResult
     */
    function validate($value);
} 