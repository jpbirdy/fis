<?php

/**
 * @desc DAO工厂类，实际上PHP中做工厂类（包括单例）意义不大
 * @author jpbirdy
 */
class Fis_App_Model_Dao_Factory
{

    private static $_daoContainer = array();

    /**
     * @param $daoName
     * @return mixed 返回不同的DAO实体
     * @throws Fis_App_Exception_AppException
     */
    static function getDAO($daoName)
    {
        if (!array_key_exists($daoName, self::$_daoContainer))
        {
            if (!class_exists($daoName))
            {
                throw new Fis_App_Exception_AppException(Fis_App_Exception_ErrCodeMapping::MODEL_DAO_NOT_EXIST);
            }
            $dao = new $daoName();
            if (!$dao instanceof Fis_App_Model_Dao_Base)
            {
                throw new Fis_App_Exception_AppException(Fis_App_Exception_ErrCodeMapping::MODEL_DAO_NOT_EXIST);
            }
            self::$_daoContainer[$daoName] = $dao;
        }
        return self::$_daoContainer[$daoName];
    }
}