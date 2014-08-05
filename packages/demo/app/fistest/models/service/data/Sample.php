<?php

/**
 * @name Service_Data_Sample
 * @desc sample data service
 * @author jpbirdy
 */
class Service_Data_Sample
{
    private $objDaoSample;

    public function __construct()
    {
        $this->objDaoSample = new Dao_Sample();
    }
}
