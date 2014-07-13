<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_DataSource_Manager {

    private static $_dsContainer = array();
    const DATA_ACCESS_LAYER = 1;
    const DB_LAYER = 2;
    const MOCK_DATASOURCE = 3;

    /**
     * @desc 根据cluster获取Db实例
     * @param string $cluster
     * @return SF_Interface_ICURDE|SF_Interface_ITranscation|SF_Interface_IDSToolOperate
     */
    static function getDBInstance($cluster = 'trade')
    {
        return self::_getDataSource(self::DB_LAYER, $cluster);
    }

    /**
     * @desc 根据cluster获取Db实例
     * @param string $cluster
     * @return SF_Interface_ICURDE
     */
    static function getDALInstance($cluster = 'trade')
    {
        return self::_getDataSource(self::DATA_ACCESS_LAYER, $cluster);
    }

    /**
     * @desc 获得mock的数据源实例
     *
     * @param string $key 标记不用的mock引擎
     * @return SF_Interface_ICURDE
     */
    static function getMockInstance($key = 'class')
    {
        return self::_getDataSource(self::MOCK_DATASOURCE, $key);
    }

    /**
     * @param $type
     * @param string $cluster
     * @return SF_Interface_ICURDE|SF_Interface_ITranscation
     */
    private static function _getDataSource($type, $cluster)
    {
        $keyName = self::getKeyName($type, $cluster);
        if (!array_key_exists($keyName, self::$_dsContainer))
        {
            switch($type)
            {
                case self::MOCK_DATASOURCE:
                    $ds = self::_buildMockDataSource();
                    break;
                case self::DATA_ACCESS_LAYER:
                    $ds = self::_buildDAL($cluster);
                    break;
                case self::DB_LAYER:
                default:
                    $ds = self::_buildDB($cluster);
                    break;
            }
            self::$_dsContainer[$keyName] = $ds ;
        }

        return self::$_dsContainer[$keyName];
    }

    /**
     * @param $type
     * @param string $cluster
     * @return string
     */
    private static function getKeyName($type, $cluster)
    {
        return $type.'_'.$cluster;
    }

    /**
     * @param string $cluster
     * @return SF_Interface_ICURDE
     */
    private static function _buildDAL($cluster)
    {
        $ds = SF_DataSource_Engine_SplitDB::getDb($cluster);
        return $ds;
    }

    /**
     * @param string $cluster
     * @return SF_Interface_ICURDE|SF_Interface_ITranscation
     */
    private static function _buildDB($cluster)
    {
        $ds = SF_DataSource_Engine_SplitDB::getDb($cluster);
        return $ds;
    }

    /**
     * @desc 构造mock的数据源
     * @return SF_DataSource_Engine_Mock
     */
    private static function _buildMockDataSource()
    {
        return new SF_DataSource_Engine_Mock();
    }


} 
