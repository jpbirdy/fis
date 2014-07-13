<?php


interface Fis_Db_ISQL
{
    // return SQL text or false on error
    public function getSQL();
}