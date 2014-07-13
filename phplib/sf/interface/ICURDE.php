<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

interface SF_Interface_ICURDE {
    /**
     * @brief 单条create接口
     *
     * @param string $table 表名
     * @param array $entry 字段
     * @param $options 选项
     *
     * @return int 返回insertId，插入后的ID
     */
    public function create($table, $entry, $options = NULL);


    /**
     * @desc 批量创建接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...)
     * @param array|null $options
     * @return int 创建条数
     */
    public function createBatch($table, $entrys, $options = NULL);


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
    public function delete($table, $conds = NULL, $options = NULL, $appends = NULL);

    /**
     * @brief select接口
     *
     * @param string $table
     * @param array $fields 字段
     * @param array $conds 条件
     * @param array $options 选项
     * @param array $appends 结尾操作
     *
     * @return array 按关联数组取回
     */
    public function select($table, $fields, $conds = NULL, $options = NULL, $appends = NULL);

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
    public function selectFromMaster($tables, $fields, $conds = NULL, $options = NULL, $appends = NULL);


    /**
     * @brief Update接口
     *
     * @param $table 表名
     * @param $row 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     * @param bool $ignoreErr 是否忽略错误
     * @return int 影响行数
     */
    public function update($table, $row, $conds = NULL,  $options = NULL, $appends = NULL, $ignoreErr = false);


    /**
     * @desc 批量更新接口
     *
     * @param string $table 表名
     * @param array $entrys 格式为array($entry,$entry,...) 每个$entry一个关联数组
     * @param array|null $options
     * @param array|null $onDup 当主键重复时候
     * @return int 创建条数
     */
    public function updateBatch($table, $entrys, $options = NULL, $onDup = NULL);

    /**
     * @brief 执行SQL接口
     *
     * @param $script 所使用的数据源的原始语句
     * @param $fetchType 结果集抽取类型
     * @param $bolUseResult 是否使用MYSQL_USE_RESULT
     *
     * @return 成功：结果数组; 失败：false
     */
    public function execute($script, $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false);
}