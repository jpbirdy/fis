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
//        echo Fis_Ip::getLocalIp();
        $this->initView();
        $content  = 'jpbirdy';
        //输出模板。下面两种方法都可以
        //后续会进行封装，通过result跳转
//        echo $this->render("test1/sample" , array('content' => $content));
        $this->display('test1/sample', array('content' => $content));

        //渲染


        $conf = Fis_Conf::getAppConf('appname');
        var_export($conf);

        Fis_Log::trace(__METHOD__);
        Fis_Log::debug(__METHOD__);
        Fis_Log::notice(__METHOD__);
        Fis_Log::warning(__METHOD__);

        $ps = new Service_Page_Sample();

        var_export($ps->execute($arrInput));


    }

}
