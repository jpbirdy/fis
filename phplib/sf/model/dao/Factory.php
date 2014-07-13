<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Model_Dao_Factory {

    private static $_daoContainer = array();

    /**
     * @desc 统一获取DAO的工厂方法
     *
     * @param $daoName 请通过Dao类的getClass方法传入
     * @return mixed 返回不同的DAO实体
     * @throws SF_Exception_InternalException
     */
    static function getDAO($daoName)
    {
        if (!array_key_exists($daoName,self::$_daoContainer))
        {
            if (!class_exists($daoName))
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::MODEL_DAO_NOT_EXIST);
            }

            $dao = new $daoName();

            if (!$dao instanceof SF_Model_Dao_Base)
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::MODEL_DAO_NOT_RIGHT);
            }

            self::$_daoContainer[$daoName] = $dao;
        }

        return self::$_daoContainer[$daoName];
    }



} 