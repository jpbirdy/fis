<?php

/**
 * @name Service_Page_Sample
 * @desc sample page service, 和action对应，组织页面逻辑，组合调用data service
 * @author jpbirdy
 */
class Service_Page_Sample
{
    private $_ds_;

    public function __construct()
    {
        $this->_ds_ = new Service_Data_Sample();
    }

    public function execute($arrInput)
    {
        Fis_Log::debug('sample page service called' . var_export($arrInput , true));
        $arrResult = array();
        $arrResult['errno'] = 0;
        $strData = Fis_Conf::getAppConf('sample/msg');
        $arrResult['data'] = $strData;
//        $arrResult['encode'] = Fis_Crypt_Rc4::rc4('jpbirdy','ENCODE','123456');
//        $arrResult['decode'] = Fis_Crypt_Rc4::rc4($arrResult['encode'],'DECODE','123456');
//
        $arrResult['encode'] = Fis_Crypt_Des::encrypt('jpbirdy');
        $arrResult['decode'] = Fis_Crypt_Des::decrypt($arrResult['encode']);
        return $arrResult;
    }
}
