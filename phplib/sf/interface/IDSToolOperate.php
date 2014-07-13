<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
interface SF_Interface_IDSToolOperate
{


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
    public function selectCount($tables, $conds = NULL, $options = NULL, $appends = NULL);


    /**
     * @desc 转换成数据源所对应的编码字符
     * @param string $string
     * @return string
     */
    public function escapeString($string);


    /**
     * @param string $key 字段名
     * @param array $ValArr 集合信息
     * @return string
     */
    public function getInString($key, $ValArr);


}