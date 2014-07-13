<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_DataSource_Engine_Mock implements SF_Interface_ICURDE,SF_Interface_ITranscation,SF_Interface_IDSToolOperate {

    private $_createRet = array();
    private $_updateRet = array();
    private $_deleteRet = array();
    private $_selectRet = array();
    private $_execRet = array();


    /**
     * @param int $createRet 创建的insertId
     * @param int $updateRet 更新的影响行数
     * @param int $deleteRet 删除影响的行数
     * @param array $selectRet 按关联数组取回，但是最外层是索引数组
     * @param mixed $execRet 执行execute方法的执行结果，希望返回什么返回什么
     * <code>
     *   $db = SF_DataSource_Manager::getMockInstance();
     *   $db->setMockData(1，1，1，array(
     *                      array('id'=>'12312')
     *              ));
     * </code>
     */
    public function setMockData($createRet,$updateRet,$deleteRet,$selectRet, $execRet = null)
    {
        $this->_createRet = $createRet;
        $this->_updateRet = $updateRet;
        $this->_deleteRet = $deleteRet;
        $this->_execRet = $execRet;

        SF_Utility_Validate_ManagerSF::createValidChain($selectRet,'mock的select data不是索引数组')->isIndexArray();
        $this->_selectRet = $selectRet;
    }

    /**
     * @brief 单条create接口
     *
     * @param string $table 表名
     * @param array $entry 字段
     * @param $options 选项
     *
     * @return int 返回insertId，插入后的ID
     */
    public function create($table, $entry, $options = NULL)
    {
        return $this->_createRet;
    }

    /**
     * @desc 批量创建接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...)
     * @param array|null $options
     * @return int 创建条数
     */
    public function createBatch($table, $entrys, $options = NULL)
    {
        return count($entrys);
    }

    /**
     * @brief delete接口
     *
     * @param $table 表名
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return int 影响条数
     */
    public function delete($table, $conds = NULL, $options = NULL, $appends = NULL)
    {
        return $this->_deleteRet;
    }

    /**
     * @brief select接口
     *
     * @param $tables 表名  array('表名1', '表名2');
     * @param $fields 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return array 按关联数组取回
     */
    public function select($tables, $fields, $conds = NULL, $options = NULL, $appends = NULL)
    {
        if (!$conds) {
            return $this->_selectRet;
        }
        
        $result = array();
        foreach ($this->_selectRet as $array) {
            $match = true;
            foreach ($conds as $key => $value) {
                $key = trim($key, '=');
                if ($array[$key] != $value) {
                    $match = false;
                }
            }

            if ($match) {
                $result[] = $array;
            }
        }

        return $result;
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
        return $this->select($tables,$fields,$conds,$options,$appends);
    }

    /**
     * @brief Update接口
     *
     * @param $table 表名
     * @param $row 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     * @param bool $ignoreErr 是否忽略错误
     * @return int 更新影响行数
     */
    public function update($table, $row, $conds = NULL, $options = NULL, $appends = NULL, $ignoreErr = false)
    {
        return $this->_updateRet;
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
        return count($entrys);
    }

    /**
     * @brief 执行SQL接口
     *
     * @param $script 所使用的数据源的原始语句
     * @param int $fetchType 结果集抽取类型
     * @param bool $bolUseResult 是否使用MYSQL_USE_RESULT
     *
     * @return void 成功：结果数组; 失败：false
     */
    public function execute($script, $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false)
    {
        return;
    }

    /**
     * 开始事务
     * @return bool
     */
    public function startTransaction()
    {
        return true;
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public function commit()
    {
       return true;
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback()
    {
        return true;
    }


    /**
     * @brief select count(*)
     *
     * @param $tables
     * @param $conds
     * @param $options
     * @param $appends
     *
     * @return int
     */
    public function selectCount($tables, $conds = NULL, $options = NULL, $appends = NULL)
    {

        $res =$this->select($tables, $conds, $options, $appends);
        return count($res);
    }

    /**
     * @desc 转换成数据源所对应的编码字符
     * @param string $string
     * @return string
     */
    public function escapeString($string)
    {
        return $string;
    }

    /**
     * @param string $key 字段名
     * @param array $ValArr 集合信息
     * @return string
     */
    public function getInString($key, $ValArr)
    {
        return '';
    }
}
