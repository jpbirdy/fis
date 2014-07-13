<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的路由协议,默认使用static路由
    }
    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册saf插件
//        $objPlugin = new Bd_Yafplugin();
//        $dispatcher->registerPlugin($objPlugin);
    }
    public function _initConfig()
    {
//        $config = Yaf_Application::app()->getConfig();
//        Yaf_Registry::set("config", $config);
    }
    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的view控制器，例如smarty,firekylin
        $dispatcher->disableView(); //禁止ap自动渲染模板
    }
    public function _initDefaultName(Yaf_Dispatcher $dispatcher)
    {
//        $dispatcher->setDefaultModule('Index')->setDefaultController('Index');
    }
}
