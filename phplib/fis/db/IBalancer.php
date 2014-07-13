<?php


interface Fis_Db_IBalancer
{
    public function select($allHosts, $key = NULL);
}