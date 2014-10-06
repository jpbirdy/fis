<?php

/**
 * @desc Action基类 ，基类只提供一些调用逻辑模型和"最"基本的校验。基类中不做任何涉及业务的转化
 * @author jpbirdy
 */
abstract class Fis_App_Action_Base extends Yaf_Action_Abstract
{

    const SUCCESS = 'success';
    const INDEX = 'index';
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const ERROR = 'error';

    const RESULT_TYPE_ACTION = 'action';
    const RESULT_TYPE_PAGE = 'page';
    const RESULT_TYPE_JSON = 'json';


    /**
     * 返回result映射，用于区分返回类型是一个页面，还是json，还是调用其他action
     * @var array
     */
    protected $_result_mapping = array();

    protected $_enable_cache = true;
    protected $_cache_connected = false;


    /**
     * 页面的返回model，正常情况都是一个数组
     * @var array
     */
    protected $_result_data = array();

    protected $_arr_request_params = null;

    protected $_session = null;

    protected $_redis = null;
    //页面级别缓存5分钟
    private $default_expire_time = 300;


    public function _getResultMapping($name)
    {
        if (isset($this->_result_mapping[$name]))
        {
            return $this->_result_mapping[$name];
        }
        else
        {
            return null;
        }
    }

    /**
     * checkSign的逻辑放入action层，一方面减轻page的压力
     * @throws Fis_App_Exception_AppException
     */
    public function _checkSign()
    {

        $ischeck = Fis_Conf::getAppConf('safe/sign');
        if ($ischeck != 'true')
        {
            return;
        }
        //
        $arrParams = self::_getAllRequestPamram();;
        $sysSign = $arrParams['sysSign'];
        $check_string = '';
        unset($arrParams['sysSign']);
        ksort($arrParams);
        foreach ($arrParams as $key => $value)
        {
            $check_string .= $key . '=' . $value;
        }
        $check_string = md5($check_string . Fis_Conf::getAppConf('safe/token'));
        if ($check_string != $sysSign)
        {
            throw new Fis_App_Exception_AppException('', Fis_App_Exception_ErrCodeMapping::SYS_ERR_SIGN);
        }
    }

    /**
     * Yaf框架Action逻辑层的实现
     */
    public function execute()
    {
        $this->_callBegin();
        $cached = false;
        try
        {
            $this->_checkSign();
            if ($this->_enable_cache && $this->_cache_connected)
            {

                $cache_data = self::_getCache();
                if (!$cache_data)
                {
                    $result_name = $this->__execute();
                    $result = $this->_getResultMapping($result_name);
                    if ($result['type'] != self::RESULT_TYPE_ACTION)
                    {
                        self::_cache($result_name);
                    }
                }
                else
                {
                    $cache_data = json_decode($cache_data, true);
                    $result_name = $cache_data['result'];
                    $result = $this->_getResultMapping($result_name);
                    $this->_result_data = $cache_data['data'];
                    $cached = true;
                }
                //统一在最后adapte
            }
            else
            {
                $result_name = $this->__execute();
                $result = $this->_getResultMapping($result_name);
            }

            $this->_adapteRetRes($result, $result_name , $cached);

        }
        catch (Exception $e)
        {
            //处理异常
            $this->display('../error/sample', array('msg' => $e->getMessage()));
        }

        $this->_callEnd();
    }

    /**
     * @desc 业务层执行部分，最后结果要return给execute
     * @return array
     */
    abstract protected function __execute();


    /**
     * 根据返回类型，采用设定的方式进行返回
     * @param $result 返回类型
     * @param $result_name 返回结果名
     * @throws Fis_App_Exception_AppException
     */
    protected function _adapteRetRes($result, $result_name , $cached = false)
    {
//        $arrRes =  Fis_App_Model_Page_Service_Base::adpatePSResultForAction($arrRes);
//        echo json_encode($arrRes);
//        var_export($this->getResultMapping());
        //跳转action不缓存
        if ($result != null)
        {
            $type = $result['type'];
            switch ($type)
            {
                case self::RESULT_TYPE_PAGE:
                    $this->display($result['result'], self::_getResultData());
                    break;
                case self::RESULT_TYPE_JSON:
                    echo json_encode(array_merge(self::_getResultData() , array('cached' => $cached)));
                    break;
                case self::RESULT_TYPE_ACTION:
                    $forward = explode('/', $result['result']);
                    $controller = current($forward);
                    $action = array_pop($forward);
                    $this->forward($controller, $action);
                    break;
                default :
                    throw new Fis_App_Exception_AppException('非合法的返回类型' , 0);
                    break;
            }
        }
        else
        {
            throw new Fis_App_Exception_AppException('不存在result=' . $result_name . '的结果，请检查是否设置', 0);
        }
    }

    /**
     * @desc 获取GET内容
     * @return array
     */
    protected function _getGetData()
    {
        return $_GET;
    }

    /**
     * @desc 获取POST内容
     * @return array
     */
    protected function _getPostData()
    {
        return $_POST;
    }

    /**
     * 获取所有请求参数，最安全的是对get和post进行合并
     * 这里不能信任request_param，因为在部分情况下，post或get的参数request_param中没有
     * @return array
     */
    public function _getAllRequestPamram()
    {
        if (is_null($this->_arr_request_params))
        {
            $this->_arr_request_params = array_merge($this->_getGetData(), $this->_getPostData());
        }
        return $this->_arr_request_params;
    }


    public function _getResultData()
    {
        return $this->_result_data;
    }

    public function _setResultData($result)
    {
        $this->_result_data = $result;
    }


    public function _getSession()
    {
        if (!($this->_session instanceof Fis_Redis_SessionRedis))
        {
            $this->_session = new Fis_Redis_SessionRedis(Fis_Conf::get('redis/master'), Fis_Conf::get('redis/slaves'));
        }
        return $this->_session;
    }


    /**
     * 缓存初始化
     * @param $master
     * @param $slaves
     * @param int $expire_time
     */
    public function _cacheInit($master, $slaves, $expire_time = 300)
    {
        if ($slaves == null || count($slaves) == 0)
        {
            $this->_redis = new Fis_Redis_Redis(false);
            $status = $this->_redis->connect($master);
        }
        else
        {
            $this->_redis = new Fis_Redis_Redis(true);
            $status = $this->_redis->connect($master, true); //master

            foreach ($slaves as $host => $port)
            {
                $this->_redis->connect(array('host' => $host, 'port' => $port), false); //SLAVE
            }
        }
        $this->default_expire_time = $expire_time;
        return $status;
    }


    /**
     * 获取缓存数据
     * @return string
     */
    public function _getCache()
    {
        if (!($this->_redis instanceof Fis_Redis_Redis))
        {
            self::_cacheInit(Fis_Conf::get('redis/master'), Fis_Conf::get('redis/slaves'));
        }
        $request_key = 'uri' . md5($_SERVER['REQUEST_URI']);
        return $this->_redis->get($request_key);
    }


    /**
     * 对URI进行缓存
     * @param $result
     * @param $data
     * @return bool
     */
    public function _cache($result = null, $data = null)
    {
        if (!$this->_enable_cache)
        {
            return false;
        }
        if ($data == null)
        {
            $data = $this->_result_data;
        }
        if (!($this->_redis instanceof Fis_Redis_Redis))
        {
            self::_cacheInit(Fis_Conf::get('redis/master'), Fis_Conf::get('redis/slaves'));
        }
        $request_key = 'uri' . md5($_SERVER['REQUEST_URI']);
        $cache_data = array('result' => $result, 'data' => $data);
        return $this->_redis->set($request_key, json_encode($cache_data), $this->default_expire_time);
    }


    private $timer = null;

    /**
     * 启动接口计时，单位us
     */
    private function _callBegin()
    {
        $this->timer = new Fis_Timer(true,Fis_Timer::PRECISION_US);
        if (!$this->_enable_cache)
        {
            $this->_cache_connected = self::_cacheInit(Fis_Conf::get('redis/master'), Fis_Conf::get('redis/slaves'));
        }

    }


    /**
     * 打印notice
     */
    private function _callEnd()
    {
        if($this->timer instanceof Fis_Timer)
        {
            $this->timer->stop();
            $time = $this->timer->getTotalTime();
            Fis_Log::addNotice('cost' , ($time / 1000 )  );
        }
        if (!$this->_enable_cache &&  $this->_cache_connected)
        {
            if ($this->_redis instanceof Fis_Redis_Redis)
            {
                $this->_redis->close();
            }
        }
        Fis_Log::notice('');
    }
}