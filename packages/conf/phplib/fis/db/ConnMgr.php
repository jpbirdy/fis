<?php
/**
 */

define('LOAD_CONF_ERROR', 10001);
define('MYSQL_FLAGS_ERROR', 10002);
define('LOAD_CLASS_ERROR', 10003);
define('SET_HOSTS_ERROR', 10004);
define('SET_HOOK_ERROR', 10005);
define('ALL_CONNECT_ERROR', 10006);
define('CONNECT_ERROR', 10007);
define('CLUSTERNAME_ERROR', 10010);

define('MYSQL_OPT_READ_TIMEOUT', 11);
define('MYSQL_OPT_WRITE_TIMEOUT', 12);

class Fis_Db_ConnMgr
{
    /**
     * @var array
     *
     * reading from configure file
     */
    private static $_conf = NULL;
    /**
     * @var array
     *
     * record both valid and failed hosts
     */
    private static $_hosts = NULL;
    /**
     * @var array
     *
     * record other data needed
     */
    private static $_dbData = NULL;

    private static $_lastDb = array();

    private static $_error = NULL;

    public static $ENABLE_PROFILING = false;

    /**
     *
     * initialize three arrays : $_conf, $_hosts, $_dbData
     *
     */
    private static function _init($clusterName)
    {
        //initialize $_conf
        self::$_conf = Fis_Conf::getConf("/db/cluster/$clusterName");
        if (self::$_conf === false || $clusterName == '')
        {
            self::$_error['errno'] = CLUSTERNAME_ERROR;
            self::$_error['error'] = 'Cannot find matched cluster:' . $clusterName . ' in configure file, please check';
            //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "ConnMgr", $clusterName, "init", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            return false;
        }

        //initialize args which use default value
        $default_conf = array(
            'retry_interval_s' => '',
            'balance_strategy' => '',
            'hook_before_query' => '',
            'hook_after_query' => '',
            'connect_timeout_s' => Intval(ceil(self::$_conf['connect_timeout_ms'] / 1000)),
            'read_timeout_s' => Intval(ceil(self::$_conf['read_timeout_ms'] / 1000)),
            'write_timeout_s' => Intval(ceil(self::$_conf['write_timeout_ms'] / 1000)),
            'retry_times' => 1,
            'charset' => '',
        );
        self::$_conf = array_merge($default_conf, self::$_conf);
        //init connect flag
        if (!array_key_exists('connect_flag', self::$_conf) || '' === self::$_conf['connect_flag'])
        {
            self::$_conf['connect_flag'] = 0;
        }
        else
        {
            $flags = explode('|', self::$_conf['connect_flag']);
            $res = '';
            foreach ($flags as $flag)
            {
                if (NULL != ($t = constant(trim($flag))))
                {
                    $res |= $t;
                }
                else
                {
                    self::$_error['errno'] = MYSQL_FLAGS_ERROR;
                    self::$_error['error'] = 'Mysql connect flags:' . trim($flag) . ' is invalid';
                    //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                    Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "ConnMgr", $clusterName, "init", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
                    return false;
                }
            }
            self::$_conf['connect_flag'] = $res;
        }

        //initialize $_dbData
        if (self::$_conf['retry_interval_s'] !== '0' && self::$_conf['retry_interval_s'] !== '')
        {
            self::$_dbData['status_manager'] = new Fis_Db_StatusManFile(DATA_PATH . '/db/');
        }
        $className = self::$_conf['balance_strategy'] === '' ? 'Fis_Db_RandBalancer' : self::$_conf['balance_strategy'];
        if (!class_exists($className))
        {
            self::$_error['errno'] = LOAD_CLASS_ERROR;
            self::$_error['error'] = 'Cannot initialize balance selector, class:' . $className . 'not exists';
            //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "ConnMgr", $clusterName, "init", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            return false;
        }
        self::$_dbData['host_selector'] = new $className();
        //initialize $_hosts
        if (!array_key_exists('host', self::$_conf))
        {
            self::$_error['errno'] = SET_HOSTS_ERROR;
            self::$_error['error'] = 'No host was setted for cluster:' . $clusterName . ' in configure file';
            //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "ConnMgr", $clusterName, "init", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            return false;
        }
        self::$_hosts['valid_hosts'] = self::$_conf['host'];
        unset(self::$_hosts['failed_hosts']);
        if (array_key_exists('status_manager', self::$_dbData))
        {
            foreach (self::$_hosts['valid_hosts'] as $key => $host)
            {
                $status = self::$_dbData['status_manager']->load($host['ip'], $host['port']);
                if (NULL !== $status && $status['last_failed_time'] + self::$_conf['retry_interval_s'] > time())
                {
                    self::$_hosts['failed_hosts'][$key]['ip'] = $host['ip'];
                    self::$_hosts['failed_hosts'][$key]['port'] = $host['port'];
                    self::$_hosts['failed_hosts'][$key]['status'] = $status;
                    unset(self::$_hosts['valid_hosts'][$key]);
                }
            }
        }
        return true;
    }

    /**
     *
     * record failed host info(failed time)in this connect
     *
     */
    private static function _recordFailedHost($index)
    {
        $status = array('last_failed_time' => time());
        self::$_hosts['failed_hosts'][$index] = self::$_hosts['valid_hosts'][$index];
        self::$_hosts['failed_hosts'][$index]['status'] = $status;
        unset(self::$_hosts['valid_hosts'][$index]);
        if (NULL !== self::$_dbData['status_manager'])
        {
            self::$_dbData['status_manager']->save(self::$_hosts['failed_hosts'][$index]['ip'], self::$_hosts['failed_hosts'][$index]['port'], self::$_hosts['failed_hosts'][$index]['status']);
        }
    }

    /**
     * @brief 获取db对象
     *
     * @param $clusterName 集群名称
     * @param $key 负载均衡key
     * @param $getNew 是否重新连接
     *
     * @return
     */
    public static function getConn($clusterName, $key = NULL, $getNew = false)
    {
        $hookBeforeInit = Fis_Conf::getConf('/db/hook_before_init');
        if ($hookBeforeInit === false)
        {
            //cannot find hookBeforeInit in conf file
            self::$_error['errno'] = LOAD_CONF_ERROR;
            self::$_error['error'] = 'Can not read hookBeforeInit, please check db/global.conf';
            //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            return false;
        }
        if ($hookBeforeInit != NULL)
        {
            //user sets hookBeforeInit
            if (is_callable($hookBeforeInit))
            {
                $clusterName = call_user_func($hookBeforeInit, $clusterName);
            }
            else
            {
                //warnning
                self::$_error['errno'] = SET_HOOK_ERROR;
                self::$_error['error'] = 'Hook(beforinit):' . $before . 'is not callable';
                //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            }
        }

        $conf = & self::$_conf;
        $hosts = & self::$_hosts;
        $dbData = & self::$_dbData;
        $lastDb = & self::$_lastDb;

        //(1) alreay save a connection (2)user do not need to recreate
        if (!empty($lastDb[$clusterName]) && !$getNew)
        {
            //Fis_Log::trace('Return an existing connection',0,array('db_cluster'=>$clusterName));
            return $lastDb[$clusterName];
        }

        if (self::_init($clusterName) === false)
        {
            return false;
        }

        //create a new db object
        $db = new Fis_Db(Fis_Db_ConnMgr::$ENABLE_PROFILING);
        //add hook
        if ('' !== ($before = $conf['hook_before_query']))
        {
            if (!$db->addHook(Fis_Db::HK_BEFORE_QUERY, $clusterName . '-before', $before))
            {
                self::$_error['errno'] = SET_HOOK_ERROR;
                self::$_error['error'] = 'Hook(befor query):' . $before . ' is not callable';
                //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            }
        }
        if ('' !== ($after = $conf['hook_after_query']))
        {
            if (!$db->addHook(Fis_Db::HK_AFTER_QUERY, $clusterName . '-after', $after))
            {
                self::$_error['errno'] = SET_HOOK_ERROR;
                self::$_error['error'] = 'Hook(after query):' . $after . ' is not callable';
                //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            }
        }
        if ('' !== ($onFail = $conf['hook_on_fail']))
        {
            if (!$db->onFail($onFail))
            {
                self::$_error['errno'] = SET_HOOK_ERROR;
                self::$_error['error'] = 'Hook(on fail):' . $onFail . ' is not callable';
                //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            }
        }

        //try to connect host until there is not host or connecting successfully
        while (true)
        {
            //balancer could not select a valid host to connect
            if (count($hosts['valid_hosts']) === 0 || ($index = $dbData['host_selector']->select($hosts, $key)) === false)
            {
                self::$_error['errno'] = ALL_CONNECT_ERROR;
                self::$_error['error'] = 'No host could be connected in the cluster';
//                 Fis_Log::warning(
//                             self::$_error['error'], 
//                             self::$_error['errno'], 
//                             array('db_cluster'=>$clusterName)
//                         );
                Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
                $hookOnConnFail = $conf['hook_on_connect_fail'];
                if ($hookOnConnFail != NULL)
                {
                    if (is_callable($hookOnConnFail))
                    {
                        call_user_func($hookOnConnFail);
                    }
                    else
                    {
                        //warnning
                        self::$_error['errno'] = SET_HOOK_ERROR;
                        self::$_error['error'] = 'Hook(on connect fail):' . $hookOnConnFail . 'is not callable';
                        //Fis_Log::warning(self::$_error['error'],self::$_error['errno']);
                        Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
                    }
                }
                return false;
            }
            //log parameters
            $logPara = array(
                'db_cluster' => $clusterName,
                'db_host' => $hosts['valid_hosts'][$index]['ip'],
                'db_port' => $hosts['valid_hosts'][$index]['port'],
                'default_db' => $conf['default_db'],
            );

            for ($i = 1; $i <= $conf['retry_times']; $i++)
            {
                $timeout = $conf['connect_timeout_s'];
                if ($timeout > 0)
                {
                    $db->setConnectTimeOut($timeout);
                }
                $r_timeout = $conf['read_timeout_s'];
                if ($r_timeout > 0)
                {
                    $db->setOption(MYSQL_OPT_READ_TIMEOUT, $r_timeout);
                }
                $w_timeout = $conf['write_timeout_s'];
                if ($w_timeout > 0)
                {
                    $db->setOption(MYSQL_OPT_WRITE_TIMEOUT, $w_timeout);
                }

                //Fis_Log::debug("retry times: $i");
                Fis_Db_RALLog::warning(RAL_LOG_DEBUG, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", 0, "retry times: $i");
                $start = microtime(true) * 1000;
                //connect
                $ret = $db->connect($hosts['valid_hosts'][$index]['ip'], $conf['username'], $conf['password'], $conf['default_db'], $hosts['valid_hosts'][$index]['port'], $conf['connect_flag']);
                $end = microtime(true) * 1000;
                if ($ret)
                {
                    if (empty($conf['charset']) || $db->charset($conf['charset']))
                    {
                        $logPara['time_ms'] = $end - $start;
                        //Fis_Log::trace('Connect to Mysql successfully',0,$logPara);
                        $lastDb[$clusterName] = $db;
                        return $lastDb[$clusterName];
                    }
                    else
                    {
                        //Fis_Log::debug('Set charset failed');
                        Fis_Db_RALLog::warning(RAL_LOG_DEBUG, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", 0, 'Set charset failed');
                    }
                }
            }
            //connect failed
            self::$_error['errno'] = CONNECT_ERROR;
            self::$_error['error'] = 'Connect to Mysql failed';
            //Fis_Log::warning(self::$_error['error'],self::$_error['errno'],$logPara);
            Fis_Db_RALLog::warning(RAL_LOG_WARN, "ConnMgr", $clusterName, "getConn", "", 0, 0, 0, 0, 0, $clusterName, "", self::$_error['errno'], self::$_error['error']);
            self::_recordFailedHost($index);
        }
        return false;
    }

    //just for testing
    private function __get($name)
    {
        return self::$$name;
    }

    /**
     * @brief 获取错误码
     *
     * @return
     */
    public static function getErrno()
    {
        return self::$_error['errno'];
    }

    /**
     * @brief 获取错误描述
     *
     * @return
     */
    public static function getError()
    {
        return self::$_error['error'];
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
