<?php
/**
 * @desc   aa
 * @author liumingjun@baidu.com
 */

class SF_Utility_Validate_ManagerSF {

    private $_value = null;
    private $_fieldName = null;
    private $_isThrowException = true;
    private $_isOptional = false;

    /**
     * @var SF_Utility_Validate_ValidateResult
     */
    private $_firstErrValidResult = null;

    /**
     * @var array
     */
    private static $_validatorContainer = array();

    /**
     * @desc 创建验证链，开始验证的第一步
     * @param mixed  $value            要验证的值、数组或对象
     * @param string $fieldName        验证的字段名，用于自动记录用
     * @param bool   $isThrowException 是抛异常模式，还是返回值模式，当为抛异常模式时，出错会抛异常，当为返回值时，需要调用函数getValidateResult获取验证结果
     * @return SF_Utility_Validate_ManagerSF
     * @throws SF_Exception_InternalException
     */
    public static function createValidChain($value, $fieldName, $isThrowException = true) {
        if (!(
            (is_object($value) || is_string($value) || is_int($value) || is_array($value) || is_float($value))
            || $value === null
            && is_string($fieldName)
            && !empty($fieldName))
        ) {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::UTILITY_VALIDATE_WRONG, '', array(
                                                                                                                   'value' => $value,
                                                                                                                   'fieldName' => $fieldName
                                                                                                              ));
        }

        $validator = new SF_Utility_Validate_ManagerSF($value, $fieldName, $isThrowException);

        return $validator;
    }

    #region base function
    /**
     * @param string $value            要验证的值
     * @param string $fieldName        验证的值的说明，用于出错提示用
     * @param bool   $isThrowException 是否是抛错模式，如果抛则会抛异常，否则返回ValidResult
     */
    private function __construct($value, $fieldName, $isThrowException) {
        $this->_value = $value;
        $this->_fieldName = $fieldName;
        $this->_isThrowException = (bool)$isThrowException;
    }

    /**
     *
     */
    public function trim() {
        $this->_value = trim($this->_value);
    }

    /**
     * @desc 判断当前验证是否通过
     * @return bool
     */
    public function isPass() {
        if (!is_null($this->_firstErrValidResult)) {
            return $this->_firstErrValidResult->isPass();
        }

        return true;
    }

    /**
     * @desc 获取验证结果，通常在非异常模式时使用。只会返回第一个验证错误的原因。
     * @return SF_Utility_Validate_ValidateResult
     */
    public function getValidateResult() {
        return $this->_firstErrValidResult;
    }

    #endregion

    #region private method
    /**
     * @return bool
     */
    private function _isEmpty() {
        return $this->_value === null || $this->_value === '';
    }

    /**
     * @param $validatorClassName
     * @throws SF_Exception_InternalException
     * @return SF_Interface_IValidate
     */
    private function getValidatorByClassName($validatorClassName) {
        if (!array_key_exists($validatorClassName, self::$_validatorContainer)) {
            $validator = new $validatorClassName();

            if (!$validator instanceof SF_Interface_IValidate) {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::UTILITY_VALIDATE_WRONG_VALIDATOR_TYPE);
            }

            self::$_validatorContainer[$validatorClassName] = $validator;
        }

        return self::$_validatorContainer[$validatorClassName];
    }

    /**
     * @desc 统一的异常处理逻辑，用于判断是抛错还是返回错误信息。
     * @param SF_Utility_Validate_ValidateResult $validateResult
     * @throws SF_Exception_BizException
     */
    private function _processException(SF_Utility_Validate_ValidateResult $validateResult) {
        if ($this->_isThrowException) {
            throw new SF_Exception_BizException(SF_Exception_ErrCodeMapping::UTILITY_VALIDATE_WRONG,
                $this->_fieldName . ' ' . $validateResult->getSummary(), $validateResult->getDetails());
        }
        else {
            if (is_null($this->_firstErrValidResult) && !$validateResult->isPass()) {
                $this->_firstErrValidResult = $validateResult;
            }
        }
    }


    /**
     * @desc 构造ValidateResult并处理异常，会自动创建ValidatorResult
     * @param string $hint    错误时提示的信息
     * @param array  $details 错误时提示的详细内容
     */
    private function buildVRandProcessException($hint, $details = array()) {
        $vr = new SF_Utility_Validate_ValidateResult($hint, $details);
        $this->_processException($vr);
    }


    /**
     * @desc 判断是否可以直接返回
     * @return bool
     */
    private function isReturn() {
        if ($this->_isOptional && $this->_isEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function _isIndexArr() {
        $this->isArray();
        $entrysKeys = array_keys($this->_value);

        $isIndex = true;
        foreach ($entrysKeys as $key) {
            if ($isIndex !== true) {
                break;
            }

            $isIndex = is_numeric($key) && $isIndex;
        }

        return array($entrysKeys, $isIndex);
    }

    #endregion

    #region validate method collection


    /**
     * @desc 表示传入该验证器的值可空，如果空时，不启用后面的验证模式。
     */
    public function optional() {
        $this->_isOptional = true;

        return $this;
    }

    /**
     * @desc 判断是否为空
     * @return $this
     */
    public function notEmpty() {
        if ($this->isReturn()) {
            return $this;
        }

        if ($this->_isEmpty()) {
            $this->buildVRandProcessException('不可为空');
        }

        return $this;
    }

    /**
     * @desc 判断是否是email形式，其中不会判空
     * @return $this
     */
    public function isEmail() {
        if ($this->isReturn()) {
            return $this;
        }
        $validatorClassName = 'SF_Utility_Validate_Validator_EMailValidatorSF';
        $validator = $this->getValidatorByClassName($validatorClassName);
        $vr = $validator->validate($this->_value);
        if (!$vr->isPass()) {
            $this->_processException($vr);
        }

        return $this;
    }

    /**
     * @desc 判断是否是有效的手机号码
     * @return $this
     */
    public function isPhoneNum() {
        if ($this->isReturn()) {
            return $this;
        }

        return $this->isMatch('~^1\d{10}$~', 'invalid phone');
    }

    /**
     * @desc 判断非0
     * @return $this
     */
    public function notZero() {
        if ($this->isReturn()) {
            return $this;
        }
        if ($this->_value == 0) {
            $vr = new SF_Utility_Validate_ValidateResult('不可为0');
            $this->_processException($vr);
        }

        return $this;
    }

    /**
     * @desc 是否是数字
     * @return $this
     */
    public function isNumeric() {
        if ($this->isReturn()) {
            return $this;
        }
        if (!is_numeric($this->_value)) {
            $this->buildVRandProcessException('不是数字,包括浮点数/整数/长整数');
        }

        return $this;
    }

    /**
     * @desc 是否>=0
     * @return $this
     */
    public function isGreaterThanOrEqualToZero() {
        if ($this->isReturn()) {
            return $this;
        }
        if ($this->_value < 0) {
            $this->buildVRandProcessException('不可小于0');
        }

        return $this;
    }

    /**
     * @desc 是否是整数、长整数或者整数的字符串，比如23 或者 '23'，支持长整数判断；
     * @return $this
     */
    public function isInt() {
        if ($this->isReturn()) {
            return $this;
        }
        if (is_int($this->_value) || is_long($this->_value) || ctype_digit($this->_value)) {
            return $this;
        }
        $this->buildVRandProcessException('不是整数或者长整数或者整数字符串');

        return $this;
    }

    /**
     * @desc 是否是合法的json格式；
     * @return $this
     */
    public function isJson() {
        if ($this->isReturn()) {
            return $this;
        }
        if (is_null(json_decode($this->_value))) {
            $this->buildVRandProcessException('不是合法的json 串');
        }

        return $this;
    }

    /**
     * @desc 是否是数组，而且不能为空;
     * @return $this
     */
    public function notEmptyArray() {
        if ($this->isReturn()) {
            return $this;
        }
        $this->isArray();
        if (empty($this->_value)) {

            $this->buildVRandProcessException('数组为空',array(
                                'field' => $this->_fieldName,
                                                'value' => $this->_value

                                                            ));

            
        }

        return $this;
    }

    /**
     * @desc 正则匹配
     * @param string $pattern 正则表达式
     * @param string $errHint 错误时的提示信息
     * @return $this
     */
    public function isMatch($pattern, $errHint) {
        if ($this->isReturn()) {
            return $this;
        }
        if (!preg_match($pattern, $this->_value)) {
            $this->buildVRandProcessException($errHint);
        }

        return $this;
    }


    /**
     * @desc 判断是否是索引数组
     */
    public function isIndexArray() {
        if ($this->isReturn()) {
            return $this;
        }
        list($entrysKeys, $isIndex) = $this->_isIndexArr();


        if (!$isIndex) {
            $this->buildVRandProcessException(
                '不是索引数组', array(
                               'keys' => $entrysKeys
                          )
            );
        }

        return $this;
    }

    /**
     * @desc 判断是否是关联数组
     * @return $this
     */
    public function isAssocArray() {
        if ($this->isReturn()) {
            return $this;
        }
        list($entrysKeys, $isIndex) = $this->_isIndexArr();
        if ($isIndex) {
            $this->buildVRandProcessException(
                '不是关联数组', array(
                               'keys' => $entrysKeys
                          )
            );
        }

        return $this;
    }

    /**
     * @desc 判断是否是数组类型
     * @return $this
     */
    public function isArray() {
        if ($this->isReturn()) {
            return $this;
        }
        if (!is_array($this->_value)) {
            $this->buildVRandProcessException('不是数组类型');
        }

        return $this;
    }

    /**
     * @param $badArr
     * @return $this
     */
    public function notContain($badArr) {
        if ($this->isReturn()) {
            return $this;
        }
        foreach ($badArr as $badStr) {
            $pos = strpos($this->_value, strtolower($badStr));
            if ($pos !== false && $pos >= 0) {
                $this->buildVRandProcessException('不能包含 ' . $badStr);
            }
        }

        return $this;
    }

    /**
     * @desc greater than $value
     * @param $value
     * @return $this
     */
    public function isGreaterThan($value) {
        if ($this->isReturn()) {
            return $this;
        }
        if ($this->_value <= $value) {
            $this->buildVRandProcessException('不可小于' . $value);
        }

        return $this;
    }

    /**
     * @desc less than $value
     * @param $value
     * @return $this
     */
    public function isLessThan($value) {
        if ($this->isReturn()) {
            return $this;
        }
        if ($this->_value >= $value) {
            $this->buildVRandProcessException('不可小于' . $value);
        }

        return $this;
    }

    /**
     * @param string|SF_Interface_IValidate $validator 可传入对象或字符串，字符串为函数名
     * @throws SF_Exception_BizException
     * @return $this
     */
    public function runExtValidator($validator) {
        if ($this->isReturn()) {
            return $this;
        }

        //传入对象
        if (is_object($validator) && $validator instanceof SF_Interface_IValidate) {
            $retRes = $validator->validate($this->_value);
        }
        //为函数名时
        elseif (is_callable($validator)) {
            $retRes = call_user_func($validator, $this->_value);
        }
        else {
            throw new SF_Exception_BizException(SF_Exception_ErrCodeMapping::UTILITY_VALIDATE_WRONG_EXT_VALIDATOR, '', array(
                                                                                                                            'extValidator' => $validator
                                                                                                                       ));

        }

        //判断返回结果类型
        if ($retRes instanceof SF_Utility_Validate_ValidateResult) {
            $this->_processException($retRes);
        }
        else if (is_bool($retRes)) {
            if (!$retRes) {
                $vr = new SF_Utility_Validate_ValidateResult();
                $this->_processException($vr);
            }
        }
        else {
            throw new SF_Exception_BizException(SF_Exception_ErrCodeMapping::UTILITY_VALIDATE_WRONG_EXT_VALIDATOR, '外部验证器返回值错误', array(
                                                                                                                                      'extValidator' => $validator
                                                                                                                                 ));
        }

        return $this;
    }

    /**
     * @param $arr
     * @throws SF_Exception_BizException
     * @return $this
     */
    public function isInArray($arr) {
        if (!is_array($arr)) {
            $this->buildVRandProcessException('不是数组类型');
        }

        if (!in_array($this->_value, $arr)) {
            $this->buildVRandProcessException('不在数组中');
        }

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function isEqual($value) {
        if (intval($this->_value) !== intval($value)) {
            $this->buildVRandProcessException(
                '两值不相等', array(
                              'ori' => $this->_value,
                              'compare' => $value,
                         )
            );
        }

        return $this;
    }

    /**
     * @param bool $allowZero
     * @return $this
     */
    public function isTimestamp($allowZero = true) {
        if ($allowZero && 0 == $this->_value) {
            return $this;
        }

        if (strtotime(date('Y-m-d H:i:s', $this->_value)) != $this->_value) {
            $this->buildVRandProcessException('时间戳不合法');
        }

        return $this;
    }


    /**
     * @param $bolResult 这里可以传入expression，然后判断是否为true，如果不为true则报错
     * @param string $hint
     */
    public function isTrue($bolResult, $hint = '')
    {

        if (!$bolResult) {
            if (empty($hint))
            {
                $hint = '不满足条件';
            }
            $this->buildVRandProcessException($hint);
        }
    }
}
