<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-21
 * Time: 下午3:00
 */
class Fis_Redis_SessionRedis
{
    protected $_redis = null;
    private $_redis_connected = false;
    //默认过期时间为1小时
    private $default_expire_time = 3600;

    //与浏览器绑定的一个key
    private $user_info = '';

    function __construct($master = null, $slaves = null, $expire_time = 1800)
    {
        // TODO: Implement __construct() method.
        if ($slaves == null || count($slaves) == 0)
        {
            $this->_redis = new Fis_Redis_Redis(false);
            $this->_redis_connected = $this->_redis->connect($master);
        }
        else
        {
            $this->_redis = new Fis_Redis_Redis(true);
            $this->_redis_connected = $this->_redis->connect($master, true); //master

            foreach ($slaves as $host => $port)
            {
                $this->_redis->connect(array('host' => $host, 'port' => $port), false); //SLAVE
            }
        }
        $this->default_expire_time = $expire_time;
        //IP+浏览器
//        $this->user_info = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
        //仅浏览器
        $this->user_info = 'session' . md5($_SERVER['HTTP_USER_AGENT']);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        if ($this->_redis_connected)
        {
            if ($this->_redis instanceof Fis_Redis_Redis)
            {
                $this->_redis->close();
            }
        }
    }


    /**
     * 从redis中获取模拟session
     * @param $key
     * @return null
     */
    public function getSession($key)
    {
        if ($this->_redis_connected)
        {
            $now_val = $this->_redis->get($this->user_info);
            if ($now_val == false)
            {
                return null;
            }
            else
            {
                $this->_redis->expire($this->user_info, $this->default_expire_time);
                $now_val = json_decode($now_val, true);
                if (array_key_exists($key, $now_val))
                {
                    return $now_val[$key];
                }
                else
                {
                    return null;
                }
            }
        }
        else
        {
            return false;
        }

    }


    /**
     * 向redis中设定session的key
     * @param $key
     * @param $value
     * @return bool
     */
    public function setSession($key, $value)
    {
        if ($this->_redis_connected)
        {
            $now_val = $this->_redis->get($this->user_info);
            if ($now_val == false)
            {
                $now_val = json_encode(array($key => $value));
            }
            else
            {
                $now_val = json_decode($now_val, true);
                $now_val[$key] = $value;
                $now_val = json_encode($now_val);
            }
            return $this->_redis->set($this->user_info, $now_val, $this->default_expire_time);
        }
        else
        {
            return false;
        }
    }
}

