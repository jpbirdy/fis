<?php

/**
 * @name Action_Sample
 * @desc 一个样例Action，包括页面返回、逻辑action跳转、json数据返回
 */
class Action_Sample extends Fis_App_Action_Base
{

    //execute返回，建议用const常量指定
    //Action_Base里已经指定的有
    //SUCCESS ERROR INDEX LOGIN LOGOUT

    const RES1 = 'res1';
    const CALL_ACTION = 'call_action';
    const JSON = 'json';



    //定义页面返回类型
    /**
     * type : page为返回一个渲染页，json为返回一个json串，action，表示进行业务跳转
     * @var array
     */
    protected $_result_mapping = array(
        self::SUCCESS => array('result' => 'sample', 'type' => Fis_App_Action_Base::RESULT_TYPE_PAGE),
        'res1' => array('result' => 'home/result1', 'type' => Fis_App_Action_Base::RESULT_TYPE_PAGE),
        'res2' => array('result' => 'home/result2', 'type' => Fis_App_Action_Base::RESULT_TYPE_PAGE),
        self::CALL_ACTION => array('result' => 'index/call', 'type' => Fis_App_Action_Base::RESULT_TYPE_ACTION),
        self::JSON => array('result' => '', 'type' => Fis_App_Action_Base::RESULT_TYPE_JSON),
    );




    protected $_result_data = array();

    /**
     * @desc 业务层执行部分，最后结果要return给execute
     * @return string
     */
    protected function __execute()
    {
        //在product中定义了library的前缀，这些前缀开头的都会从library目录中获取类
        //其他的默认在phplib中获取类
        $test = new Fistest_Sample();
        $test = new Base_Sample();

        Fis_Log::trace(__METHOD__ . var_export(self::_getAllRequestPamram(),true) );

        self::_setResultData(array('content' => 'hello world' ));

//        $timer = new Fis_Timer(true,Fis_Timer::PRECISION_US);

        $redis = new Fis_Redis_Redis();
        $redis->connect(array('host'=>'127.0.0.1','port'=>6379));

//        $timer->stop();
//        echo (int)$timer->getTotalTime() . '<br>';
//        echo ($redis->hGet('sessions' , 'key12345'));
//        echo $redis->get('foo');
        $session = self::_getSession();

        $session->setSession('user' , 'jpbirdy');
//        var_export($session->getSession('user'));
        return self::SUCCESS;
    }
}
