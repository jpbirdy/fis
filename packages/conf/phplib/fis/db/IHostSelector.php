<?php

interface Fis_Db_IHostSelector
{
    public function select(Fis_Db_DBMan $dbman, $key = NULL);
}