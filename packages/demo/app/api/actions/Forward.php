<?php

/**
 * @name Action_Sample
 * @desc sample action, 和url对应
 */
class Action_Forward extends Yaf_Action_Abstract
{
    public function execute()
    {
        $this->forward('api','sample');
    }

}
