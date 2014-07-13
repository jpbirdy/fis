<?php

interface Fis_Db_IStatusMan
{
    public function load($host, $port);

    public function save($host, $port, $status);

    public function clean($host, $port);

    public function cleanAll();
}