<?php

class Fis_Db_RandSelector implements Fis_Db_IHostSelector
{
    /**
     * @brief 随机选择接口
     *
     * @param $dbman dbman对象
     * @param $key 选择key
     *
     * @return
     */
    public function select(Fis_Db_DBMan $dbman, $key = NULL)
    {
        if (!count($dbman->validHosts))
        {
            return false;
        }
        return array_rand($dbman->validHosts);
    }
}