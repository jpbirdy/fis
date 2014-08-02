<?php

/**
 * @desc 错误映射类，包含了系统的错误码和对应错误信息
 * @author jpbirdy
 */
class Fis_App_Exception_ErrCodeMapping
{

    /**
     * @desc 根据需求增加的映射关系，在子类中实现，当键名相同时会覆盖系统的映射。
     * @return array
     */
    protected function _getAdditionCodeMsgMapping()
    {
        return array();
    }
    /**
     * @desc 返回所属app的数字前缀
     * @return int
     */
    protected function _getPrefix()
    {
        return 0;
    }

    /**
     * @desc 不带前缀的错误码映射。当存在时候，抛出的异常码将是被映射的
     * @return array array('现在的错误码'=>'被映射的错误码')
     */
    protected function _getOldCodeMapping()
    {
        return array();
    }

    //不带前缀的错误码
    const SYSTEM_OK = 0;
    const SYS_ERR_UNKNOWN = -1; //未知错误;
    const SYS_ERR_PARAM = 1; //参数错误;
    const SYS_ERR_SIGN = 2; //签名错误;

    const SYS_ERR_CURL = 3; //CURL错误;
    const MODEL_DAO_NOT_EXIST = 41; //DAO类异常;



    /**
     * @param int $code 传入的是不带前缀的错误
     * @return int
     */
    final public function getCodeWithPrefix($code)
    {
        $prefix = $this->_getPrefix();
        if (!empty($prefix))
        {
            if (array_key_exists($code, $this->_getMappingRelation()))
            {
                //采用+或|而不用.因为错误码为int类型，防止在系统中层数嵌套过多造成溢出
                //同时确保了在业务层抛出错误码的范围
                return $prefix + $code;
            }
        }
        return $code;
    }
    /**
     * @desc 系统级别的错误，即使在业务层抛出也不添加前缀，
     * @param int $code
     * @return int
     */
    final public function getDisplayCode($code)
    {
        $baseArr = $this->_getCodeMsgMapping();
        if (array_key_exists($code, $baseArr))
        {
            $retCode = $code;
        }
        elseif (array_key_exists($code, $this->_getMappingRelation()))
        {
            $retCode = $this->getCodeWithPrefix($code);
        }
        else
        {
            $retCode = $code;
        }
        return (int)($retCode);
    }


    /**
     * @desc 系统默认的错误码映射表
     * @return array
     */
    private function _getCodeMsgMapping()
    {
        return array(
            self::SYS_ERR_UNKNOWN => '未知错误',
            self::SYS_ERR_PARAM => '参数错误',
            self::SYS_ERR_SIGN => '签名错误',
            self::SYS_ERR_CURL => 'CURL错误',
            self::MODEL_DAO_NOT_EXIST => '未找到DAO类',
        );
    }

    /**
     * 得到系统错误映射和业务错误映射
     * @return array
     */
    final private function _getMappingRelation()
    {
        $baseArr = $this->_getCodeMsgMapping();
        $addArr = $this->_getAdditionCodeMsgMapping();
        return $baseArr + $addArr;
    }

    /**
     * @desc 获取错误信息
     * @param int $code , string extendMsg
     * @return string
     */
    public function getDisplayErrMsgByCode($code, $extendMsg)
    {
        $retMsg = '';
        $codeMsgMapping = $this->_getMappingRelation();
        if (array_key_exists($code, $codeMsgMapping))
        {
            $retMsg = $codeMsgMapping[$code];
        }
        if ($extendMsg == null || trim($extendMsg) == '')
        {
            return $retMsg;
        }
        else
        {
            return $extendMsg;
        }
    }
}