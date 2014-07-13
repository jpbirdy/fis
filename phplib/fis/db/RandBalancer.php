<?php

class Fis_Db_RandBalancer implements Fis_Db_IBalancer
{
    /**
     * @brief 选择host
     *
     * @param $allHosts 全部Host
     * @param $key 选择key
     *
     * @return
     */
    public function select($allHosts, $key = NULL)
    {
        if (!count($allHosts['valid_hosts']))
        {
            return false;
        }
        return array_rand($allHosts['valid_hosts']);
    }
}