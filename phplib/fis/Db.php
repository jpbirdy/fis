<?php

define('INVALID_SQL', 10008);
define('QUERY_ERROR', 10009);

class Fis_Db
{
    const T_NUM = 'n';
    const T_NUM2 = 'd';
    const T_STR = 's';
    const T_RAW = 'S';
    const T_RAW2 = 'r';
    const V_ESC = '%';

    // hook types
    const HK_BEFORE_QUERY = 0;
    const HK_AFTER_QUERY = 1;

    // query result types
    const FETCH_RAW = 0; // return raw mysqli_result
    const FETCH_ROW = 1; // return numeric array
    const FETCH_ASSOC = 2; // return associate array
    const FETCH_OBJ = 3; // return Fis_DBResult object

    const LOG_SQL_LENGTH = 30;

    private $mysql = NULL;
    private $dbConf = NULL;
    private $isConnected = false;
    private $lastSQL = NULL;

    private $enableProfiling = false;
    private $arrCost = NULL;
    private $lastCost = 0;
    private $totalCost = 0;

    private $hkBeforeQ = array();
    private $hkAfterQ = array();
    private $onfail = NULL;

    private $sqlAssember = NULL;
    private $_error = NULL;

    //SplitDB info
    private $strDBName = NULL;
    private $splitDB = NULL;


    public function __construct($enableProfiling = false)
    {
        $this->mysql = mysqli_init();
        if ($enableProfiling)
        {
            $this->enableProfiling(true);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function enableSplitDB($strDBName, $strConfPath, $strConfFilename)
    {
        $this->strDBName = $strDBName;
        $this->splitDB = new Fis_DB_SplitDB($strDBName, $strConfPath, $strConfFilename);
        if (is_null($this->splitDB))
        {
            return false;
        }
        return true;
    }

    /**
     * @brief 设置mysql连接选项
     *
     * @param $optName 选项名字
     * @param $value   选项值
     *
     * @return
     */
    public function setOption($optName, $value)
    {
        return $this->mysql->options($optName, $value);
    }

    /**
     * @brief 设置连接超时
     *
     * @param $seconds 超时时间
     *
     * @return
     */
    public function setConnectTimeOut($seconds)
    {
        if ($seconds <= 0)
        {
            return false;
        }
        return $this->setOption(MYSQLI_OPT_CONNECT_TIMEOUT, $seconds);
    }

    public function __get($name)
    {
        switch ($name)
        {
            case 'error':
                return $this->mysql->error;
            case 'errno':
                return $this->mysql->errno;
            case 'insertID':
                return $this->mysql->insert_id;
            case 'affectedRows':
                return $this->mysql->affected_rows;
            case 'lastSQL':
                return $this->lastSQL;
            case 'lastCost':
                return $this->lastCost;
            case 'totalCost':
                return $this->totalCost;
            case 'isConnected':
                return $this->isConnected;
            case 'db':
                return $this->mysql;
            default:
                return NULL;
        }
    }

    /**
     * @brief 连接方法
     *
     * @param $host 主机
     * @param $uname 用户名
     * @param $passwd 密码
     * @param $dbname 数据库名
     * @param $port 端口
     * @param $flags 连接选项
     *
     * @return true：成功；false：失败
     */
    public function connect($host, $uname = NULL, $passwd = NULL, $dbname = NULL, $port = NULL, $flags = 0)
    {
        $port = intval($port);
        if (!$port)
        {
            $port = 3306;
        }

        $this->dbConf = array(
            'host' => $host,
            'port' => $port,
            'uname' => $uname,
            'passwd' => $passwd,
            'flags' => $flags,
            'dbname' => $dbname,
        );

        $this->isConnected = $this->mysql->real_connect($host, $uname, $passwd, $dbname, $port, NULL, $flags);

        if (!$this->isConnected)
        {
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $dbname, "connect", "$host:$port", 0, 0, 0, 0, 0, $dbname, "", $this->mysql->errno, $this->mysql->error);
        }

        return $this->isConnected;
    }

    public function ralConnect($strName)
    {
        $ret = ral_get_service($strName);

        $intServerNum = array_rand($ret['server']);

        $server = $ret['server'][$intServerNum];
        $this->dbConf = array(
            'host' => $server['ip'],
            'port' => $server['port'],
            'uname' => $ret['user'],
            'passwd' => base64_decode($ret['passwd']),
            'flags' => 0,
            'dbname' => $ret['extra']['dbname'],
        );

        $this->isConnected = $this->mysql->real_connect($this->dbConf['host'], $this->dbConf['uname'], $this->dbConf['passwd'], $this->dbConf['dbname'], $this->dbConf['port'], NULL, $this->dbConf['flags']);

        if (!$this->isConnected)
        {
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $this->$dbConf['dbname'], "connect", "{$this->$dbConf['host']}:{$this->$dbConf['port']}", 0, 0, 0, 0, 0, $this->$dbConf['dbname'], "", $this->mysql->errno, $this->mysql->error);
        }

        return $this->isConnected;
    }

    /**
     * @brief 重新连接
     *
     * @return true：成功；false：失败
     */
    public function reconnect()
    {
        if ($this->dbConf === NULL)
        {
            return false;
        }
        $conf = $this->dbConf;

        $this->isConnected = $this->mysql->real_connect($conf['host'], $conf['uname'], $conf['passwd'], $conf['dbname'], $conf['port'], NULL, $conf['flags']);

        if (!$this->isConnected)
        {
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $conf['dbname'], "reconnect", "{$conf['host']}:{$conf['port']}", 0, 0, 0, 0, 0, $conf['dbname'], "", $this->mysql->errno, $this->mysql->error);
        }

        return $this->isConnected;
    }

    /**
     * @brief 关闭连接
     *
     * @return
     */
    public function close()
    {
        if (!$this->isConnected)
        {
            return;
        }
        $this->isConnected = false;
        $this->mysql->close();
    }

    /**
     * @brief 是否连接，注意，此时mysqli.reconnect需要被关闭
     *
     * @param $bolCheck
     *
     * @return
     */
    public function isConnected($bolCheck = false)
    {
        if ($this->isConnected && $bolCheck && !$this->mysql->ping())
        {
            $this->isConnected = false;
        }
        return $this->isConnected;
    }

    /**
     * @brief 查询接口
     *
     * @param $sql 查询sql
     * @param $fetchType 结果集抽取类型
     * @param $bolUseResult 是否使用MYSQLI_USE_RESULT
     *
     * @return 结果数组：成功；false：失败
     */
    public function query($sql, $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false)
    {
        /*
        if(!$this->isConnected())
        {
            return false;
        }
        */
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->doSql($sql, $fetchType, $bolUseResult);
        }

        $logPara = array(
            'db_host' => $this->dbConf['host'],
            'db_port' => $this->dbConf['port'],
            'default_db' => $this->dbConf['dbname'],
        );

        if (!is_string($sql))
        {
            // get sql text
            if (!($sql instanceof Fis_Db_ISQL) || !($sql = $sql->getSQL()))
            {
                $this->_error['errno'] = INVALID_SQL;
                $this->_error['error'] = 'Input SQL is not valid,please use string or ISQL instance';
                Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $this->dbConf['dbname'], "query", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], $sql, INVALID_SQL, 'Input SQL is not valid,please use string or ISQL instance');
                return false;
            }
        }

        // execute hooks before query
        foreach ($this->hkBeforeQ as $arrCallback)
        {
            $func = $arrCallback[0];
            $extArgs = $arrCallback[1];
            if (call_user_func_array($func, array($this, &$sql, $extArgs)) === false)
            {
                return false;
            }
        }

        $this->lastSQL = $sql;

        $beg = intval(microtime(true) * 1000000);

        $res = $this->mysql->query($sql, $bolUseResult ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT);

        // record cost
        $this->lastCost = intval(microtime(true) * 1000000) - $beg;
        $this->totalCost += $this->lastCost;

        Fis_Db_RALLog::notice(RAL_LOG_SUM_SUCC, "Fis_DB", $this->dbConf['dbname'], "query", "{$this->dbConf['host']}:{$this->dbConf['port']}", round($this->lastCost / 1000), 0, round($this->lastCost / 1000), 0, 0, $this->dbConf['dbname'], $sql, 1);

        // do profiling
        if ($this->enableProfiling)
        {
            $this->arrCost[] = array($sql, $this->lastCost);
        }

        $ret = false;

        $pos = strpos($sql, "\n");
        if ($pos)
        {
            $logPara['sql'] = strstr($sql, array("\n", ' '));
        }
        else
        {
            $logPara['sql'] = $sql;
        }

        // res is NULL if mysql is disconnected
        if (is_bool($res) || $res === NULL)
        {
            $ret = ($res == true);
            // call fail handler
            if (!$ret)
            {
                $this->_error['errno'] = QUERY_ERROR;
                $this->_error['error'] = 'Query failed';
                Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $this->dbConf['dbname'], "query", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], $logPara['sql'], $this->mysql->errno, $this->mysql->error);

                if ($this->onfail !== NULL)
                {
                    call_user_func_array($this->onfail, array($this, &$ret));
                }
            }
        }
        // we have result
        else
        {

            switch ($fetchType)
            {
                case Fis_Db::FETCH_OBJ:
                    $ret = new Fis_Db_DBResult($res);
                    break;

                case Fis_Db::FETCH_ASSOC:
                    $ret = array();
                    while ($row = $res->fetch_assoc())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                case Fis_Db::FETCH_ROW:
                    $ret = array();
                    while ($row = $res->fetch_row())
                    {
                        $ret[] = $row;
                    }
                    $res->free();
                    break;

                default:
                    $ret = $res;
                    break;
            }
        }

        // execute hooks after query
        foreach ($this->hkAfterQ as $arrCallback)
        {
            $func = $arrCallback[0];
            $extArgs = $arrCallback[1];
            call_user_func_array($func, array($this, &$ret, $extArgs));
        }

        return $ret;
    }

    /**
     * @brief 格式化查询接口
     *
     * @return
     */
    public function queryf( /* $sql_fmt, ..., $fetchType = Fis_DB::FETCH_ASSOC, $bolUseResult = false */)
    {
        $arrArgs = func_get_args();

        if (($argNum = count($arrArgs)) == 0)
        {
            return false;
        }

        $fmt = $arrArgs[0];
        $fmtLen = strlen($fmt);
        $sql = '';
        $cur = 1;
        $next_pos = 0;

        while (true)
        {
            $esc_pos = strpos($fmt, Fis_Db::V_ESC, $next_pos);
            if ($esc_pos === false)
            {
                $sql .= substr($fmt, $next_pos);
                break;
            }

            $sql .= substr($fmt, $next_pos, $esc_pos - $next_pos);

            $esc_pos++;
            $next_pos = $esc_pos + 1;

            if ($esc_pos == $fmtLen)
            {
//                echo "no char after '%'\n";
                return false;
            }

            $type_char = $fmt{$esc_pos};

            if ($type_char != Fis_Db::V_ESC)
            {
                if ($argNum <= $cur)
                {
//                    echo "no enough args\n";
                    return false;
                }
                $arg = $arrArgs[$cur++];
            }

            switch ($type_char)
            {
                case Fis_Db::T_NUM:
                case Fis_Db::T_NUM2:
                    $sql .= intval($arg);
                    break;

                case Fis_Db::T_STR:
                    $sql .= $this->escapeString($arg);
                    break;

                case Fis_Db::T_RAW:
                case Fis_Db::T_RAW2:
                    $sql .= $arg;
                    break;

                case Fis_Db::V_ESC:
                    $sql .= Fis_Db::V_ESC;
                    break;

                default:
//                echo "unknow type: $type_char\n";
                    return false;
            }
        }

        $fetchType = Fis_Db::FETCH_ASSOC;
        $bolUseResult = false;

        if ($argNum > $cur)
        {
            $fetchType = $arrArgs[$cur++];
        }

        if ($argNum > $cur)
        {
            $bolUseResult = $arrArgs[$cur++];
        }

        return $this->query($sql, $fetchType, $bolUseResult);
    }

    private function __getSQLAssember()
    {
        if ($this->sqlAssember == NULL)
        {
            $this->sqlAssember = new Fis_Db_SQLAssember($this);
        }
        return $this->sqlAssember;
    }

    /**
     * @brief select接口
     *
     * @param $tables 表名
     * @param $fields 字段名
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     * @param $fetchType 获取类型
     * @param $bolUseResult 是否使用MYSQL_USE_RESULT
     *
     * @return
     */
    public function select($tables, $fields, $conds = NULL, $options = NULL, $appends = NULL, $fetchType = Fis_Db::FETCH_ASSOC, $bolUseResult = false)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getSelect($tables, $fields, $conds, $options, $appends);
        if (!$sql)
        {
            return false;
        }
        return $this->query($sql, $fetchType, $bolUseResult);
    }

    /**
     * @brief select count(*)接口
     *
     * @param $tables 表名
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return
     */
    public function selectCount($tables, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $fields = 'COUNT(*)';
        $sql = $this->sqlAssember->getSelect($tables, $fields, $conds, $options, $appends);
        if (!$sql)
        {
            return false;
        }
        $res = $this->query($sql, Fis_Db::FETCH_ROW);
        if ($res === false)
        {
            return false;
        }
        return intval($res[0][0]);
    }

    /**
     * @brief Insert接口
     *
     * @param $table 表名
     * @param $row 字段
     * @param $options 选项
     * @param $onDup 键冲突时的字段值列表
     *
     * @return
     */
    public function insert($table, $row, $options = NULL, $onDup = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getInsert($table, $row, $options, $onDup);
        if (!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @brief Update接口
     *
     * @param $table 表名
     * @param $row 字段
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return
     */
    public function update($table, $row, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getUpdate($table, $row, $conds, $options, $appends);
        if (!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @brief delete接口
     *
     * @param $table 表名
     * @param $conds 条件
     * @param $options 选项
     * @param $appends 结尾操作
     *
     * @return
     */
    public function delete($table, $conds = NULL, $options = NULL, $appends = NULL)
    {
        $this->__getSQLAssember();
        $sql = $this->sqlAssember->getDelete($table, $conds, $options, $appends);
        if (!$sql || !$this->query($sql))
        {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @brief prepare查询接口
     *
     * @param $query 查询语句
     * @param $getRaw 是否返回原始的mysqli_stmt对象
     *
     * @return
     */
    public function prepare($query, $getRaw = false)
    {
        $stmt = $this->mysql->prepare($query);

        Fis_Db_RALLog::notice(RAL_LOG_SUM_SUCC, "Fis_DB", $this->dbConf['dbname'], "prepare", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], $query, 1);

        if ($stmt === false)
        {
            Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "Fis_DB", $this->dbConf['dbname'], "prepare", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], $query, $this->mysql->errno, $this->mysql->error);
            return false;
        }
        if ($getRaw)
        {
            return $stmt;
        }
        else
        {
            return new Fis_Db_DBStmt($stmt);
        }
    }

    /**
     * @brief 获取上一次SQL语句
     *
     * @return
     */
    public function getLastSQL()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->getLastSQL();
        }

        return $this->lastSQL;
    }

    /**
     * @brief 获取Insert_id
     *
     * @return
     */
    public function getInsertID()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->getInsertID();
        }

        return $this->mysql->insert_id;
    }

    /**
     * @brief 获取受影响的行数
     *
     * @return
     */
    public function getAffectedRows()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->getAffectedRows();
        }

        return $this->mysql->affected_rows;
    }
    /*
        public function getLastQueryInfo()
        {
            return $this->mysql->info;
        }
    */

    /**
     * @brief 添加查询Hook
     *
     * @param $where 钩子类型（HK_BEFORE_QUERY or HK_AFTER_QUERY）
     * @param $id 钩子id
     * @param $func 钩子函数
     * @param $extArgs 钩子函数参数
     *
     * @return
     */
    public function addHook($where, $id, $func, $extArgs = NULL)
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->addHook($where, $id, $func, $extArgs);
        }

        switch ($where)
        {
            case self::HK_BEFORE_QUERY:
                $dest = & $this->hkBeforeQ;
                break;
            case self::HK_AFTER_QUERY:
                $dest = & $this->hkAfterQ;
                break;
            default:
                return false;
        }
        if (!is_callable($func))
        {
            return false;
        }
        $dest[$id] = array($func, $extArgs);
        return true;
    }

    /**
     * @brief 查询、设置和移除失败处理句柄
     *
     * @param $func 0表示查询当前的失败处理句柄，NULL清除当前的失败处理句柄，其他则设置当前的失败处理句柄
     *
     * @return
     */
    public function onFail($func = 0)
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->onFail($func);
        }

        if ($func === 0)
        {
            return $this->onfail;
        }
        if ($func === NULL)
        {
            $this->onfail = NULL;
            return true;
        }
        if (!is_callable($func))
        {
            return false;
        }
        $this->onfail = $func;
        return true;
    }

    /**
     * @brief 移除钩子
     *
     * @param $where 钩子类型（HK_BEFORE_QUERY or HK_AFTER_QUERY）
     * @param $id 钩子id
     *
     * @return
     */
    public function removeHook($where, $id)
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->removeHook($where, $id);
        }

        switch ($where)
        {
            case self::HK_BEFORE_QUERY:
                $dest = & $this->hkBeforeQ;
                break;
            case self::HK_AFTER_QUERY:
                $dest = & $this->hkAfterQ;
                break;
            default:
                return false;
        }
        if (!array_key_exists($id, $dest))
        {
            return false;
        }
        unset($dest[$id]);
        return true;
    }

    //////////////////////////// profiling ////////////////////////////

    /**
     * @brief 获取上一次耗时
     *
     * @return
     */
    public function getLastCost()
    {
        return $this->lastCost;
    }

    /**
     * @brief 获取本对象至今的总耗时
     *
     * @return
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @brief 获取profiling数据
     *
     * @return
     */
    public function getProfilingData()
    {
        return $this->arrCost;
    }

    /**
     * @brief 清除profiling数据
     *
     * @return
     */
    public function cleanProfilingData()
    {
        $this->arrCost = NULL;
    }

    /**
     * @brief 设置和查询当前profiling开关状态
     *
     * @param $enable NULL返回当前状态，其他设置当前状态
     *
     * @return
     */
    public function enableProfiling($enable = NULL)
    {
        if ($enable === NULL)
        {
            return $this->enableProfiling;
        }
        $this->enableProfiling = ($enable == true);
    }

    //////////////////////////// transaction ////////////////////////////

    /**
     * @brief 设置或查询当前自动提交状态
     *
     * @param $bolAuto NULL返回当前状态，其他设置当前状态
     *
     * @return
     */
    public function autoCommit($bolAuto = NULL)
    {
        if ($bolAuto === NULL)
        {
            $sql = 'SELECT @@autocommit';
            $res = $this->query($sql);
            if ($res === false)
            {
                return NULL;
            }
            return $res[0]['@@autocommit'] == '1';
        }

        return $this->mysql->autocommit($bolAuto);
    }

    /**
     * @brief 开始事务
     *
     * @return
     */
    public function startTransaction()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->startTransaction();
        }

        $sql = 'START TRANSACTION';
        return $this->query($sql);
    }

    /**
     * @brief 提交事务
     *
     * @return
     */
    public function commit()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->commit();
        }

        $ret = $this->mysql->commit();

        Fis_Db_RALLog::notice(RAL_LOG_SUM_SUCC, "Fis_DB", $this->dbConf['dbname'], "commit", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], "", 0);

        return $ret;
    }

    /**
     * @brief 回滚
     *
     * @return
     */
    public function rollback()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->rollback();
        }

        $ret = $this->mysql->rollback();

        Fis_Db_RALLog::notice(RAL_LOG_SUM_SUCC, "Fis_DB", $this->dbConf['dbname'], "rollback", "{$this->dbConf['host']}:{$this->dbConf['port']}", 0, 0, 0, 0, 0, $this->dbConf['dbname'], "", 0);

        return $ret;
    }

    //////////////////////////// util ////////////////////////////

    /**
     * @brief 基于当前连接的字符集escape字符串
     *
     * @param $string 输入字符串
     *
     * @return
     */
    public function escapeString($string)
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->escapeString($string);
        }
        return $this->mysql->real_escape_string($string);
    }

    /**
     * @brief 选择db
     *
     * @param $dbname 数据库名
     *
     * @return
     */
    public function selectDB($dbname)
    {
        if ($this->mysql->select_db($dbname))
        {
            $this->dbConf['dbname'] = $dbname;
            return true;
        }
        return false;
    }

    /**
     * @brief 获取当前db中存在的表
     *
     * @param $pattern 表名Pattern
     * @param $dbname 数据库
     *
     * @return
     */
    public function getTables($pattern = NULL, $dbname = NULL)
    {
        $sql = 'SHOW TABLES';
        if ($dbname !== NULL)
        {
            $sql .= ' FROM ' . $this->escapeString($dbname);
        }
        if ($pattern !== NULL)
        {
            $sql .= ' LIKE \'' . $this->escapeString($pattern) . '\'';
        }

        if (!($res = $this->query($sql, false)))
        {
            return false;
        }

        $ret = array();
        while ($row = $res->fetch_row())
        {
            $ret[] = $row[0];
        }
        $res->free();
        return $ret;
    }

    /**
     * @brief 检查数据表是否存在
     *
     * @param $name 表名
     * @param $dbname 数据库名
     *
     * @return
     */
    public function isTableExists($name, $dbname = NULL)
    {
        $tables = $this->getTables($name, $dbname);
        if ($tables === false)
        {
            return NULL;
        }
        return count($tables) > 0;
    }

    /*
        public function changeUser($uname, $passwd, $dbname = NULL)
        {
            if(!$this->isConnected())
            {
                return false;
            }

            if($this->dbConf['uname'] == $name &&
                $this->dbConf['passwd'] == $passwd)
            {
                if($dbname !== NULL)
                {
                    return $this->selectDB($dbname);
                }
                return true;
            }

            $ret = $this->mysql->change_user($uname, $passwd, $dbname);
            if($ret)
            {
                $this->dbConf['uname'] = $uname;
                $this->dbConf['passwd'] = $passwd;
                $this->dbConf['dbname'] = $dbname;
            }
            return $ret;
        }
    */

    /**
     * @brief 设置和查询当前连接的字符集
     *
     * @param $name NULL表示查询，字符串表示设置
     *
     * @return
     */
    public function charset($name = NULL)
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->charset($name);
        }

        if ($name === NULL)
        {
            return $this->mysql->character_set_name();
        }
        return $this->mysql->set_charset($name);
    }

    /**
     * @brief 获取连接参数
     *
     * @return
     */
    public function getConnConf()
    {
        if ($this->dbConf == NULL)
        {
            return NULL;
        }

        return array(
            'host' => $this->dbConf['host'],
            'port' => $this->dbConf['port'],
            'uname' => $this->dbConf['uname'],
            'dbname' => $this->dbConf['dbname']
        );
    }

    //////////////////////////// error ////////////////////////////

    /**
     * @brief 获取当前mysqli错误码
     *
     * @return
     */
    public function errno()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->getMysqlErrno();
        }

        return $this->mysql->errno;
    }

    /**
     * @brief 获取当前mysqli错误描述
     *
     * @return
     */
    public function error()
    {
        //if enable splitdb
        if (isset($this->splitDB))
        {
            return $this->splitDB->getMysqlError();
        }

        return $this->mysql->error;
    }

    /**
     * @brief 获取db库错误码
     *
     * @return
     */
    public function getErrno()
    {
        return $this->_error['errno'];
    }

    /**
     * @brief 获取db库错误描述
     *
     * @return
     */
    public function getError()
    {
        return $this->_error['error'];
    }
    /*
        public function ansiError()
        {
            return $this->mysql->sqlstate;
        }
    */
    //////////////////////////// env ////////////////////////////
    /*
        public static function getClientVersion($bolNum = true)
        {
            if($bolNum)
            {
                return mysqli_get_client_version();
            }
            else
            {
                return mysqli_get_client_info();
            }
        }

        public function getServerVersion($bolNum = true)
        {
            if($bolNum)
            {
                return $this->mysql->server_version;
            }
            else
            {
                return $this->mysql->server_info;
            }
        }

        public function getServerStat($detail = false)
        {
            if($detail === false || $detail === NULL)
            {
                return $this->mysql->stat();
            }

            $sql = 'SHOW STATUS';
            if(!($res = $this->query($sql, false)))
            {
                return false;
            }

            // get full detail
            if($detail === true)
            {
                $ret = array();
                while($row = $res->fetch_row())
                {
                    $ret[$row[0]] = $row[1];
                }
            }
            // get one detail
            else
            {
                $ret = false;
                while($row = $res->fetch_row())
                {
                    if($detail == $row[0])
                    {
                        $ret = $row[1];
                    }
                }
            }

            $res->free();
            return $ret;
        }

        public function getThreadID()
        {
            return $this->mysql->thread_id;
        }

        public function getHostInfo()
        {
            if($this->dbConf === NULL)
            {
                return NULL;
            }

            return array(
                'host' => $this->dbConf['host'],
                'port' => $this->dbConf['port']
                );
        }

        public function getProtocol()
        {
            return $this->mysql->protocol_version;
        }
    */
}
