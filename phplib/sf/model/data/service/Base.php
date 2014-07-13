<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Model_Data_Service_Base {

    /**
     * @return SF_Interface_ILog
     */
    protected function logger()
    {
        return SF_Log_Manager::getBDLogger(SF_Log_Manager::LOGGER_DEPTH);
    }

} 