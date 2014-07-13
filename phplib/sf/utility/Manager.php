<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

final class SF_Utility_Manager {

    #region private method
    /**
     * @var array 纯然所有单例的容器
     */
    private static $_singleInstanceContainer = array();
    /**
     * @统一管理单例
     *
     * @param string $className
     * @throws SF_Exception_InternalException
     * @return mixed
     */
    private static function getSingleInstance($className)
    {
        if(!array_key_exists($className, self::$_singleInstanceContainer))
        {
            $singleInstance = new $className();

            if ($singleInstance instanceof SF_Utility_SingleInstanceSF) {
                self::$_singleInstanceContainer[$className] = $singleInstance;
            }
            else
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ASPECT_FUNC_DEFINE_ERROR);
            }
        }

        return self::$_singleInstanceContainer[$className];
    }
    #endregion

    /**
     * @desc 创建验证链，开始验证的第一步
     *
     * @param mixed $value 要验证的值、数组或对象
     * @param string $fieldName 验证的字段名，用于自动记录用
     * @param bool $isThrowException 是抛异常模式，还是返回值模式，当为抛异常模式时，出错会抛异常，当为返回值时，需要调用函数getValidateResult获取验证结果
     * @return SF_Utility_Validate_ManagerSF
     * @throws SF_Exception_InternalException
     */
    static function validator($value, $fieldName, $isThrowException = true)
    {
        return SF_Utility_Validate_ManagerSF::createValidChain($value,$fieldName,$isThrowException);
    }

    /**
     * @desc 数据字典
     * @return SF_Utility_Helper_DataContextSF
     */
    static function dataWatcher()
    {
        return self::getSingleInstance(SF_Utility_Helper_DataContextSF::getClass());
    }

    /**
     * @desc 返回请求外部连接器
     * @return SF_Utility_Helper_RequestServiceSF
     */
    static function requestor()
    {
        return self::getSingleInstance(SF_Utility_Helper_RequestServiceSF::getClass());
    }

    /**
     * @desc 返回请求外部连接器
     * @return SF_Utility_Helper_RequestServiceSF
     */
    static function idGenerator()
    {
        return self::getSingleInstance(SF_Utility_Helper_RequestServiceSF::getClass());
    }

    /**
     * @desc 返回配置管理类
     * @return SF_Utility_Helper_ConfigurationSF
     */
    static function configuration()
    {
        return self::getSingleInstance(SF_Utility_Helper_ConfigurationSF::getClass());
    }

    /**
     * @desc 返回配置管理类
     * @return SF_Utility_Helper_ConfigurationSF
     */
    static function asyncSubmitor()
    {
        return self::getSingleInstance(SF_Utility_Helper_AsyncSubmitSF::getClass());
    }

    /**
     * @desc 返回配置管理类
     * @return SF_Utility_Helper_TextProcessorSF
     */
    static function textProcessor()
    {
        return self::getSingleInstance(SF_Utility_Helper_TextProcessorSF::getClass());
    }



}