<?php

/**
 * @name Action_Sample
 * @desc sample action, 和url对应
 */
class Action_Sample extends Yaf_Action_Abstract
{
    public function execute()
    {
        $arrInput = array_merge($_GET, $_POST);
        var_export($arrInput);
        echo Fis_Ip::getLocalIp();
        $this->initView();
        $content  = 'jpbirdy';
        echo $this->render("sample" , array('content' => $content));
    }

}
