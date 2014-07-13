<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ITranscation {
    /**
     * 开始事务
     * @return bool
     */
    public function startTransaction();

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit();

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback();
} 