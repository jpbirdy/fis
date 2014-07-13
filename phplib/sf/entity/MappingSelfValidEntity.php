<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 *
 *
 */

abstract class SF_Entity_MappingSelfValidEntity extends SF_Entity_MappingEntityBase implements SF_Interface_ISelfValidate{

    protected $isDebug = false;

    /**
     * @desc 自动构建具有映射关系的实体
     * @param array|int $data
     * @param bool $notNeedSelfValidate
     * @param bool $ignoreErr
     */
    function __construct($data = self::WITHOUT_DATA_TOKEN, $notNeedSelfValidate = true ,$ignoreErr =true)
    {
        if (!$this->isWithoutData($data))
        {
            $this->instantiate($data, $notNeedSelfValidate, $ignoreErr);
        }
    }

    /**
     * @abstract
     * @desc hook在初始之前
     * @param $inputData 初始化时传入的数据
     */
    protected function _beforeInit(&$inputData)
    {

    }

    /**
     * @abstract
     * @desc hook在验证之前
     * @param $inputData 初始化时传入的数据
     */
    protected function _beforeValidate(&$inputData)
    {

    }


    /**
     * @abstract
     * @desc 在transToArr之后运行的函数，用于调整函数transToArr返回的数组值
     * @notice 一定要使用$arr来调整，不可以在其中使用$this
     * @param array $arr transToArr出来的数组
     */
    protected function _afterToArray(&$arr)
    {

    }

    /**
     * @desc 根据传入的数据初始化该对象
     *
     * @param array $inputData 初始化时传入的数据
     * @param bool $isNeedSelfValidate
     * @param bool $notCheckAllProsMatch 是否检验传入的数据和实体中的映射属性完全一致
     * @return SF_Entity_MappingSelfValidEntity
     */
    public function instantiate($inputData, $isNeedSelfValidate = true, $notCheckAllProsMatch = true)
    {
        $this->clear();
        if ($this->isDebug)
        {
            $mockInputData = $this->_mockInitData();
            if (!empty($mockInputData))
            {
                $inputData = $mockInputData;
            }
        }
        $this->_beforeInit($inputData);
        $this->_instantiate($inputData,$notCheckAllProsMatch);
        $this->_beforeValidate($inputData);
        if ($isNeedSelfValidate) {
            $this->validate();
        }
        return $this;
    }

    /**
     * @abstract
     * @desc mock掉Model的初始化数据
     * @return array
     */
    protected function _mockInitData()
    {
        return array();
    }
}




