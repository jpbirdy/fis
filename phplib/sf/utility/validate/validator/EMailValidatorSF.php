<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Validate_Validator_EMailValidatorSF implements SF_Interface_IValidate {

    /**
     * @param mixed $value
     * @return SF_Utility_Validate_ValidateResult
     */
    function validate($value)
    {
        $emailPattern = "/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/";

        preg_match($emailPattern, $value, $email);

        if ($value != $email[0])
        {
            return new SF_Utility_Validate_ValidateResult('email不符合格式');
        }
        else
        {
            return new SF_Utility_Validate_ValidateResult();
        }
    }
}