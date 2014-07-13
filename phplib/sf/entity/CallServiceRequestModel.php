<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 *
 * @property string $psSeg
 * @property string $token
 * @property array $data
 */

class SF_Entity_CallServiceRequestModel extends SF_Entity_RequestModel
{
    const REQUEST_PARAM_OP = 'op';
    const REQUEST_PARAM_TOKEN = 'token';
    const REQUEST_PARAM_DATA = 'data';

    /**
     * @desc 返回 属性和字段的映射关系 ‘api字段'=>’属性'
     * @return array
     */
    protected function mapFieldsPros()
    {
        return array(
            self::REQUEST_PARAM_OP => 'psSeg',
            self::REQUEST_PARAM_TOKEN => self::REQUEST_PARAM_TOKEN,
            self::REQUEST_PARAM_DATA => self::REQUEST_PARAM_DATA,
        );
    }

    /**
     * @desc hook在初始之前
     * @param $inputData 初始化时传入的数据
     */
    protected function _beforeInit(&$inputData)
    {

        if (isset($inputData[self::REQUEST_PARAM_DATA]))
        {
            SF_Utility_Manager::validator($inputData[self::REQUEST_PARAM_DATA], 'callservice data')->isJson();
            $inputData[self::REQUEST_PARAM_DATA] = json_decode($inputData[self::REQUEST_PARAM_DATA], true);
        }

    }

    function validate()
    {

        //SF_Utility_Validate_ManagerSF::createValidChain($this->psSeg, self::REQUEST_PARAM_OP)->notEmpty();
        //SF_Utility_Validate_ManagerSF::createValidChain($this->token, self::REQUEST_PARAM_OP)->notEmpty();
        //SF_Utility_Validate_ManagerSF::createValidChain($this->data, self::REQUEST_PARAM_OP)->notEmpty();
    }

} 