<?php
/**
 * @desc 离线脚本的基础类
 * @author liumingjun@baidu.com
 */

class SF_Script_Base {

    /**
     * @return SF_Interface_ILog
     */
    protected function logger()
    {
        return SF_Log_Manager::getBDLogger(SF_Log_Manager::LOGGER_DEPTH);
    }

    /**
     * @param string $cluster
     * @return SF_Interface_ICURDE|SF_Interface_IDSToolOperate|SF_Interface_ITranscation
     */
    protected function getDB($cluster = 'trade')
    {
        return SF_DataSource_Manager::getDBInstance($cluster);
    }

} 