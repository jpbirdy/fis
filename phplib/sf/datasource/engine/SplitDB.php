<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_DataSource_Engine_SplitDB extends SF_DataSource_Engine_DB
{
    const TABLE_INDEX = 'trade_table_index';


    /**
     * @desc 获得DB连接实例
     * @param string $cluster
     * @return mixed
     */
    public static function getDb($cluster = 'ClusterOne')
    {
        if (!isset(self::$dbFactory[$cluster])) {
            self::$dbFactory[$cluster] = new SF_DataSource_Engine_SplitDB($cluster);
        }
        return self::$dbFactory[$cluster];
    }

    #region 当要增加拆表的数量时，改动以下内容
    //拆表前缀
    const PRETAB_CPS_ORDER = 'trade_cps_order';
    const PRETAB_ORDER = 'trade_order';
    const PRETAB_COUPON = 'trade_coupon';
    const PRETAB_ORDER_EXT = 'trade_order_ext';

    //拆表的对应类型
    const TYPE_CPS_ORDER = 1;
    const TYPE_ORDER = 2;
    const TYPE_COUPON = 3;
    const TYPE_ORDER_EXT = 4;

    //拆表记录数
    const TOTAL_CPS_ORDER = 40000000;
    const TOTAL_ORDER = 40000000;
    const TOTAL_COUPON = 40000000;
    const TOTAL_ORDER_EXT = 40000000;

    /**
     * @desc 键名为需要拆分的表，键值为对应的拆分阈值
     *
     * @var array
     */
    private $_needSplitArray = array(
        self::PRETAB_CPS_ORDER => self::TOTAL_CPS_ORDER,
        self::PRETAB_ORDER => self::TOTAL_ORDER,
        self::PRETAB_COUPON => self::TOTAL_COUPON,
        self::PRETAB_ORDER_EXT => self::TOTAL_ORDER_EXT,
    );



    /**
     * @param string $table 原始表名，未拆分的
     * @return int
     */
    private function _getTypeByTable($table)
    {
        $mapping = array(
            self::PRETAB_CPS_ORDER => self::TYPE_CPS_ORDER,
            self::PRETAB_ORDER => self::TYPE_ORDER,
            self::PRETAB_COUPON => self::TYPE_COUPON,
            self::PRETAB_ORDER_EXT => self::TYPE_ORDER_EXT,
        );

        return $mapping[$table];
    }
    #endregion


    const CONCAT_SYMBOL = '_';

    const CUR_TABLE = 'cur_table';
    const PRE_TABLE = 'pre_table';
    const NXT_TABLE = 'nxt_table';
    const TOTAL = 'total';

    protected $indexTableEntry = null;

    /**
     * @desc 如果不需要分表，就会直接返回$table
     *
     * @param string $table
     * @return string
     */
    public function getCurTable($table)
    {

        return $this->_getIndexTableProp($table, self::CUR_TABLE);
    }

    /**
     * @desc 如果不需要分表，就会直接返回$table
     *
     * @param string $table
     * @return string
     */
    public function getNxtTable($table)
    {
        return $this->_getIndexTableProp($table, self::NXT_TABLE);
    }

    /**
     * @desc 如果不需要分表，就会直接返回$table
     *
     * @param $table
     * @return string
     */
    public function getPreTable($table)
    {
        return $this->_getIndexTableProp($table, self::PRE_TABLE);
    }

    /**
     * @param $table
     * @return int
     */
    public function getEntryTotal($table)
    {
        return $this->_getIndexTableProp($table, self::TOTAL);
    }

    /**
     * @param $table
     * @param $key
     * @return mixed
     */
    private function _getIndexTableProp($table, $key)
    {

        $trueKey = $this->_getRealKey($table, $key);
        if (!$this->_isNeedSplit($table)) {
            return $table;
        }

        if (empty($this->indexTableEntry) || !isset($this->indexTableEntry[$trueKey])) {
            $this->_initIndexPropsByTable($table);
        }

        return $this->indexTableEntry[$trueKey];
    }

    /**
     * @param string $table
     * @param string $key
     * @return string
     */
    private function _getRealKey($table, $key)
    {
        return $table.$key;
    }

    /**@desc 初始化indexTable的数据内容
     * @param $table 原始表名，未拆分过的
     */
    private function _initIndexPropsByTable($table)
    {
        $fields = array(
            self::CUR_TABLE, self::PRE_TABLE, self::NXT_TABLE, self::TOTAL,
        );

        $indexRes = $this->select(self::TABLE_INDEX, $fields, array('type=' => $this->_getTypeByTable($table)), null, null);

        $this->indexTableEntry[$this->_getRealKey($table, self::CUR_TABLE)] = $indexRes[0][self::CUR_TABLE];
        $this->indexTableEntry[$this->_getRealKey($table, self::PRE_TABLE)] = $indexRes[0][self::PRE_TABLE];
        $this->indexTableEntry[$this->_getRealKey($table, self::NXT_TABLE)] = $indexRes[0][self::NXT_TABLE];
        $this->indexTableEntry[$this->_getRealKey($table, self::TOTAL)] = $indexRes[0][self::TOTAL];
    }

    /**
     * @desc 按表获取拆分的数量
     * @param string $table 原始表名，未拆分的
     * @return int
     */
    private function _getSplitTotalByTable($table)
    {
        return $this->_needSplitArray[$table];
    }

    /**
     * @param $table 原始表名，未拆分的
     * @return bool
     */
    private function _isNeedSplit($table)
    {
        return array_key_exists($table, $this->_needSplitArray);
    }

    /**
     * @param $table
     * @return bool
     */
    private function isNeedFindInPreTable($table)
    {
        $preTable = $this->getPreTable($table);
        return $this->_isNeedSplit($table) && !empty($preTable);
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
        $processCount = 1;
        $ret = parent::create($this->getCurTable($table), $row, $options);
        if ($this->_isNeedSplit($table)) {
            $this->updateIndexTableByTableName($table, $processCount);
        }
        return $ret;
    }

    /**
     * @param string $table 原始表名，未分表的
     * @param $processCount
     */
    private function updateIndexTableByTableName($table, $processCount)
    {
        //大于拆表记录数进行拆表，已经插入数据成功后调用，需要拆表限制数值减1
        $total = $this->getEntryTotal($table) + $processCount;
        if ($this->getEntryTotal($table) < $this->_getSplitTotalByTable($table) && $total >= $this->_getSplitTotalByTable($table)) {
            $nxtTable = $this->getNewTable($table, $total);
            $arrFields = array(
                'update_time' => time(),
                self::PRE_TABLE => $this->getCurTable($table),
                self::CUR_TABLE => $this->getNxtTable($table),
                self::NXT_TABLE => $nxtTable,
            );
        }
        else {
            //只更新total和update_time
            $arrFields = array(
                'update_time' => time()
            );
        }

        $arrFields [] = self::TOTAL . '=' . self::TOTAL . '+' . $processCount;

        parent::update(self::TABLE_INDEX, $arrFields, array('type=' => $this->_getTypeByTable($table)));
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
        $res = parent::update($this->getCurTable($table), $row, $conds, $options, $appends, $ignoreErr);

        if ($this->isNeedFindInPreTable($table)) {
            $res = parent::update($this->getPreTable($table), $row, $conds, $options, $appends, $ignoreErr);
        }

        return $res;
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
        $res = parent::delete($this->getCurTable($table), $conds, $options, $appends);

        if ($this->isNeedFindInPreTable($table)) {
            $res = parent::delete($this->getPreTable($table), $conds, $options, $appends);
        }

        return $res;
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
     * @return array
     */
    public function select(
        $table, $fields, $conds = NULL, $options = NULL, $appends = NULL,
        $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false
    )
    {

        $res = parent::select($this->getCurTable($table), $fields, $conds, $options, $appends, $fetchType, $bolUseResult);
        $otherRes = array();
        if ($this->isNeedFindInPreTable($table)) {
            $otherRes = parent::select($this->getPreTable($table), $fields, $conds, $options, $appends, $fetchType, $bolUseResult);
        }

        $res = array_merge($res , $otherRes);

        return $res;
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

        $entryCount = count($entrys);

        $isNeedSeperateTwoBatch = false;
        $nowTableIndex = '';
        if ($this->_isNeedSplit($table)) {
            $nowTableIndex = round($this->getEntryTotal($table) / $this->_getSplitTotalByTable($table));

            //为了避免整除，所以加了1
            $newTableIndex = round(($this->getEntryTotal($table) + $entryCount + 1) / $this->_getSplitTotalByTable($table));

            if ($newTableIndex > $nowTableIndex) {
                $isNeedSeperateTwoBatch = true;
            }
        }

        if ($isNeedSeperateTwoBatch) {
            //此时要拆开，分批批量进行
            $firstEntryCount = ($nowTableIndex + 1) * $this->_getSplitTotalByTable($table) - $this->getEntryTotal($table);
            $firstEntrys = array_slice($entrys, 0, $firstEntryCount - 1);
            $firstRes = parent::createBatch($this->getCurTable($table), $firstEntrys, $options);
            $this->updateIndexTableByTableName($table, $firstEntryCount);

            $secondEntryCount = $entryCount - $firstEntryCount;
            $secondEntrys = array_slice($entrys, $firstEntryCount, $secondEntryCount - 1);
            $finalTotal = $this->getEntryTotal($table) + $entryCount;
            $secondRes = parent::createBatch($this->getNewTable($table, $finalTotal), $secondEntrys, $options);

            $this->updateIndexTableByTableName($table, $secondEntryCount);

            return intval($firstRes) + intval($secondRes);


        }
        else {
            $res = parent::createBatch($this->getCurTable($table), $entrys, $options);

            if ($this->_isNeedSplit($table)) {
                $this->updateIndexTableByTableName($table, $entryCount);
            }

            return intval($res);
        }
    }

    /**
     * @desc 批量更新接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...)
     * @param array|null $options
     * @param array|null $onDup 传的是获取的条件 array(key1,key2),各个之间是and关系
     * @return int 创建条数
     */
    public function updateBatch($table, $entrys, $options = NULL, $onDup = NULL)
    {
        return parent::updateBatch($this->getCurTable($table),$entrys,$options,$onDup);

        //拼贴对应数量的update语句执行。
        if (empty($entrys))
        {
            return 0;
        }
        $fieldDelimiter = ',';
        $tableTemplate = 'TABLETEMPLATE';
        $allUpdateSql = '';
        foreach ($entrys as $entry) {
            $updateField = 'update '.$tableTemplate.' set ';

            foreach ($entry as $field => $value) {
                if (is_string($value))
                {
                    $value = '"'.$value.'"';
                }

                $updateField = $updateField . $field . '='. $value. $fieldDelimiter;
            }
            $updateField = rtrim($updateField, $fieldDelimiter). ' ';

            $conditionSql = 'where ';

            foreach ($onDup as $key) {
                $conditionSql = $conditionSql . $key.'='.$entry[$key].$fieldDelimiter;
            }

            $conditionSql =  rtrim($conditionSql, $fieldDelimiter). ';';

            $allUpdateSql = $allUpdateSql . $updateField . $conditionSql;
        }

        $firstSql = str_replace($tableTemplate,$this->getCurTable($table),$allUpdateSql);
        $secondSql = str_replace($tableTemplate,$this->getPreTable($table),$allUpdateSql);
        $firstRows = $this->execute($firstSql);
        $secondRows = $this->execute($secondSql);

        return $firstRows + $secondRows;
    }

    /**
     * @param $table 原始表名
     * @param $total
     * @return string
     */
    private function getNewTable($table, $total)
    {
        $newTable = $table . self::CONCAT_SYMBOL . strval(ceil($total / $this->_getSplitTotalByTable($table) + 1));
        return $newTable;
    }


}
