<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Validate_ValidateResult {


    private $_summary = null;
    private $_details;

    /**
     * @param string $summary 错误信息摘要
     * @param array $details 详细的错误信息，k-v型的数组
     */
    function __construct($summary = '', $details = array())
    {
        $this->_summary = $summary;
        $this->_details = $details;

    }

    /**
     * @desc 获取错误的摘要
     * @return string
     */
    public function getSummary()
    {
        return $this->_summary;
    }


    /**
     * @desc 获取详细的错误信息，关联数组
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * @desc 判断是否通过验证
     * @return bool
     */
    public function isPass()
    {
        return empty($this->_details) && empty($this->_summary);
    }



} 