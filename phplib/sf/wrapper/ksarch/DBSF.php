<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Wrapper_KSArch_DBSF implements SF_Interface_ITranscation, SF_Interface_ICURDE, SF_Interface_IDSToolOperate
{

    /**
     * @var Fis_Db
     */
    protected $_db = null;
    protected static $dbFactory = array();
    /**
     * @var SF_Interface_ITimer
     */
    private $timer = null;
    private $sqlPerformance = true;


    private $_isSetTransaction = false;

    private $_isNeedCommitForSelectFromMaster = false;

    private function _setTransactionFlag()
    {
        $this->_isSetTransaction = true;
    }

    private function _unsetTransactionFlag()
    {
        $this->_isSetTransaction = false;
    }


    protected function _isAlreadyTransaction()
    {
        return $this->_isSetTransaction;
    }
    /**
     * @param string $cluster
     * @throws SF_Exception_InternalException
     */
    protected function __construct($cluster = 'ClusterOne')
    {
        $this->_db = Bd_Db_ConnMgr::getConn($cluster);
        if (empty($this->_db)) {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::SYSTEM_DB_CREATE_CONNECTION_ERROR, "KSARCH_MONITOR_DB create cluster($cluster) instance failed", array(
                'cluster' => $cluster,
                'config' => Bd_Db_ConnMgr::getConn($cluster)
            ));
        }

        if ($this->sqlPerformance) {
            $this->timer = new SF_Wrapper_KSArch_Timer();
        }
    }

    /**
     * @desc 获得DB连接实例
     * @param string $cluster
     * @return mixed
     */
    public static function getDb($cluster = 'ClusterOne')
    {
        if (!isset(self::$dbFactory[$cluster])) {
            self::$dbFactory[$cluster] = new SF_Wrapper_KSArch_DBSF($cluster);
        }
        return self::$dbFactory[$cluster];
    }

    /**
     * 开始事务
     * @return bool
     */
    public function startTransaction()
    {
        $ret = $this->_db->startTransaction();
        Bd_Log::debug("Transcation:" . $this->_db->getLastSQL().'[start]',0,null,3);
        $this->_setTransactionFlag();
        $this->_checkError();
        return $ret;
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit()
    {
        $ret = $this->_db->commit();
        $this->_unsetTransactionFlag();
        Bd_Log::debug("Transcation:" . $this->_db->getLastSQL().'[commit]',0,null,3);
        $this->_checkError();
        return $ret;
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback()
    {
        $ret = $this->_db->rollback();
        $this->_unsetTransactionFlag();
        Bd_Log::debug("Transcation:" . $this->_db->getLastSQL().'[rollback]',0,null,3);
        $this->_checkError();
        return $ret;
    }

    /**
     * @desc 基于当前连接的字符集escape字符串
     *
     * @param string $string
     * @return string
     */
    public function escapeString($string)
    {
        return $this->_db->escapeString($string);
    }

    /**
     * @brief 执行SQL接口
     *
     * @param $sql 要执行的sql语句
     * @param int $fetchType 结果集抽取类型
     * @param bool $bolUseResult 是否使用MYSQL_USE_RESULT
     *
     * @throws SF_Exception_DataSourceExecute
     * @return array|\Bd_Db_DBResult|bool 成功：结果数组; 失败：false
     */
    public function execute($sql, $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_db->query($sql, $fetchType, $bolUseResult);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().'[execute]',0,null,3);
        }
        $this->_checkError();
        return $ret;
    }

    /**
     * @brief create接口
     *
     * @param string $table 数据库表名
     * @param array $row 字段
     * @param $options 选项
     *
     * @throws SF_Exception_DataSourceExecute
     * @return bool|int
     */
    public function create($table, $row, $options = NULL)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_db->insert($table, $row, $options, NULL);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().'[create]',0,null,3);
        }
        $this->_checkError();
        return $ret;
    }

    /**
     * @brief Update接口
     *
     * @param $table 数据库表名
     * @param $row 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     * @param bool $ignoreErr 是否忽略错误
     *
     * @return bool|int
     */
    public function update($table, $row, $conds = NULL, $options = NULL, $appends = NULL, $ignoreErr = false)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_db->update($table, $row, $conds, $options, $appends);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().'[update]',0,null,3);
        }
        if (!$ignoreErr) {
            $this->_checkError();
        }
        return $ret;

    }

    /**
     * @brief delete接口
     *
     * @param $table 数据库表名
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @throws SF_Exception_DataSourceExecute
     * @return bool|int
     */
    public function delete($table, $conds = NULL, $options = NULL, $appends = NULL)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_db->delete($table, $conds, $options, $appends);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().'[delete]',0,null,3);
        }


        $this->_checkError();
        return $ret;
    }


    /**
     * @brief select接口
     *
     * @param $table 表名  array('表名1', '表名2');
     * @param $fields 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     * @param int $fetchType 获取类型
     * @param bool $bolUseResult 是否使用MYSQL_USE_RESULT
     *
     * @throws SF_Exception_DataSourceExecute
     * @return array|\Bd_Db_DBResult|bool
     */
    public function select(
        $table, $fields, $conds = NULL, $options = NULL, $appends = NULL,
        $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false
    )
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $res = $this->_db->select($table, $fields, $conds, $options,
            $appends, $fetchType, $bolUseResult);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().'[select]',0,null,3);
        }
        $this->_checkError();
        return $res;
    }

    /**
     * @brief select count(*)靠
     *
     * @param $tables 靠
     * @param $conds 靠
     * @param $options 靠
     * @param $appends 靠靠
     *
     * @throws SF_Exception_DataSourceExecute
     * @return bool|int
     */
    public function selectCount($tables, $conds = NULL, $options = NULL, $appends = NULL)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_db->selectCount($tables, $conds, $options, $appends);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().' [selectCount]',0,null,3);
        }

        $this->_checkError();
        return $ret;
    }

    /**
     * @throws SF_Exception_DataSourceExecute
     */
    private function _checkError()
    {
        $errno = $this->_db->errno();
        $error = $this->_db->error();

        if (!empty($error) || !empty($errno)) {
            throw new SF_Exception_DataSourceExecute('', array(
                'error' => $error,
                'errno' => $errno,
                'sql' => $this->_db->getLastSQL()

            ));
        }
    }

    /**
     * @desc 从主库Select内容的方法
     *
     * @param $tables 表名  array('表名1', '表名2');
     * @param $fields 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return array 按关联数组取回
     */
    public function selectFromMaster($tables, $fields, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $appends = array_merge(array(' FOR UPDATE'), $appends);
        $enableSplitTranscation = false;
        if (!$this->_isAlreadyTransaction())
        {
            $this->startTransaction();
            $this->_isNeedCommitForSelectFromMaster = true;
            $enableSplitTranscation = true;

        }
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $this->select($tables, $fields, $conds, $options, $appends);
        if ($this->_isNeedCommitForSelectFromMaster)
        {
            $this->commit();
            $this->_isNeedCommitForSelectFromMaster = false;
        }
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().' [transaction]'.$enableSplitTranscation,0,null,3);
        }

    }

    /**
     * @desc 批量创建接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...)
     * @param array|null $options
     * @throws SF_Exception_InternalException
     * @return int 创建条数
     */
    public function createBatch($table, $entrys, $options = NULL)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_batchInsert($table, $entrys, $options);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().' [createBatch]',0,null,3);
        }
        return $ret;

    }

    /**
     * @desc 批量更新接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...)
     * @param array|null $options
     * @param array|null $onDup 当主键重复时候
     * @return int 创建条数
     */
    public function updateBatch($table, $entrys, $options = NULL, $onDup = NULL)
    {
        if ($this->sqlPerformance) {
            $this->timer->start();
        }
        $ret = $this->_batchInsert($table, $entrys, $options, $onDup);
        if ($this->sqlPerformance) {
            $timeUsed = $this->timer->stop();
            Bd_Log::debug("timeUsed( $timeUsed us) SQL:" . $this->_db->getLastSQL().' [updateBatch]',0,null,3);
        }
        return $ret;
    }


    /**
     * @param $table
     * @param $entrys
     * @param null $options
     * @param array|null $onDup 键值对
     * @throws SF_Exception_InternalException
     * @return int
     */
    private function _batchInsert($table, $entrys, $options = NULL, $onDup = NULL)
    {
        $isIndexArr = SF_Utility_Validate_ManagerSF::createValidChain($entrys, 'batch create entry', false)->notEmptyArray()->isIndexArray()->isPass();
        if (!$isIndexArr) {
            throw new SF_Exception_DataSourceExecute('批量插入传入的参数不为索引数组', array(
                'table' => $table,
                'entrys' => $entrys,
                'option' => $options
            ));
        }

        //校验批量更新的参数是否全部一致

        $fields = null;
        $allValues = array();

        foreach ($entrys as $entry) {
            $isFirstRun = is_null($fields);
            if ($isFirstRun) {
                $fields = array();
            }
            //初始化标准的字段名
            $values = array();
            foreach ($entry as $field => $value) {
                if ($isFirstRun) {
                    $fields [] = $field;
                }

                if (!$isFirstRun && !in_array($field, $fields)) {
                    throw new SF_Exception_DataSourceExecute('批量插入传入的数组，键名存在不一致的情况', array(
                        'table' => $table,
                        'entrys' => $entrys,
                        'wrongField' => $field,
                        'fields' => $fields
                    ));
                }

                $values [] = $this->getColValue($this->escapeString($value));
            }

            if (count($fields) !== count($entry)) {
                throw new SF_Exception_DataSourceExecute('批量插入传入的数组纬度不匹配', array(
                    'table' => $table,
                    'entrys' => $entrys,
                    'option' => $options
                ));
            }

            $valuesStr = '(' . implode(',', $values) . ')';
            $allValues [] = $valuesStr;

        }

        $allValuesStr = implode(',', $allValues);
        $colsStr = ' (' . implode(',', $fields) . ')';

        $duplicate = '';
        if (!is_null($onDup)) {
            $duplicate = 'ON DUPLICATE KEY UPDATE ';


            foreach ($onDup as $name => $value) {
                if (is_int($name)) {
                    $duplicate .= "$value, ";
                }
                else {
                    if (!is_int($value)) {
                        if ($value === NULL) {
                            $value = 'NULL';
                        }
                        else {
                            $value = $this->escapeString($value);
                        }
                    }
                    $duplicate .= "$name=$value, ";
                }
            }
            $duplicate = substr($duplicate, 0, strlen($duplicate) - 2);
        }

        $sql = 'INSERT INTO ' . $table . $colsStr . ' VALUES ' . $allValuesStr . $duplicate . ';';
        $res = $this->execute($sql);

        if ($res === false) {
            throw new SF_Exception_DataSourceExecute('批量插入失败', array(
                'table' => $table,
                'entrys' => $entrys,
                'option' => $options,
                'sql' => $sql,
                'error' => $this->_db
            ));
        }

        return $this->_db->getAffectedRows();
    }

    /**
     * @param string $key 字段名
     * @param array $ValArr 集合信息
     * @return string
     */
    public function getInString($key, $ValArr)
    {
        SF_Utility_Validate_ManagerSF::createValidChain($ValArr, 'getInString valArr')->isIndexArray();
        return $key . ' IN ('.implode(',',$ValArr).')';
    }

    /**
     * @desc 根据类型返回，数据库使用的字符串值
     * @param mixed $val
     * @return mixed|string
     */
    private function getColValue($val)
    {
        if (is_string($val))
        {
            return '\''.$val.'\'';
        }
        return $val;
    }

    public function getLastSQL()
    {
        return $this->_db->getLastSQL();
    }
}

