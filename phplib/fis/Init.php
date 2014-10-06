<?php

/**
 * brief of Init.php:
 * Fis全局初始化类。
 */
class Fis_Init
{
    public static $app = null;

    public static function init($app_name = null)
    {
        // 初始化基础环境
        self::initBasicEnv();
        // 初始化App环境
        self::initAppEnv($app_name);
//	    self::initAutoLoader();
        // 初始化Yaf框架
        self::initYaf();
        return self::$app;
    }

    private static function initBasicEnv()
    {
        // 页面启动时间(us)，PHP5.4可用$_SERVER['REQUEST_TIME']
        define('REQUEST_TIME_US', intval(microtime(true) * 1000000));

        // ODP预定义路径
        $path = realpath((dirname(__FILE__) . '/../../../'));
        define('ROOT_PATH', $path);
        // CONF_PATH是文件系统路径，不能传给Fis_Conf
//        define('CONF_PATH', ROOT_PATH . '/conf');
        define('DATA_PATH', ROOT_PATH . '/data');
        define('BIN_PATH', ROOT_PATH . '/php/bin');
        define('LOG_PATH', ROOT_PATH . '/log');
        define('TPL_PATH', ROOT_PATH . '/template');
        define('LIB_PATH', ROOT_PATH . '/php/phplib');
        define('WEB_ROOT', ROOT_PATH . '/webroot');
        define('PHP_EXEC', BIN_PATH . '/php');
        return true;
    }

    private static function getAppName()
    {
        // /xxx/index.php
        //$script = explode('/', $_SERVER['SCRIPT_NAME']);
        //某些重写规则会导致"/xxx/index.php/"这样的SCRIPT_NAME
        $app_name = null;
        $script = explode('/', rtrim($_SERVER['SCRIPT_NAME'], '/'));
        if (count($script) == 3 && $script[2] === 'index.php')
        {
            $app_name = $script[1];
        }
        return $app_name;
    }

    private static function initAppEnv($app_name)
    {
        // 检测当前App
        if ($app_name != null || ($app_name = self::getAppName()) != null)
        {
            define('MAIN_APP', $app_name);
        }
        else
        {
            define('MAIN_APP', 'unknown-app');
        }
        define('APP_PATH', ROOT_PATH . '/app/' . MAIN_APP);
        // 设置当前App
        require_once LIB_PATH . '/fis/AppEnv.php';
        Fis_Appenv::setCurrApp(MAIN_APP);
        return true;
    }

    // 初始化类自动加载
    // 自动初始化交给Yaf框架完成
    // 在PHP.INI中添加yaf.library
    private static function initAutoLoader()
    {
    }

    // 初始化Ap
    private static function initYaf()
    {
        self::$app = new Yaf_Application(APP_PATH . '/conf/application.ini');
        return true;
    }


}
