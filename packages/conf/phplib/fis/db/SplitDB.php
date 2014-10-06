<?php
/***************************************************************************
 *
*   Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
*
***************************************************************************/




/**
 * SplitDB
 * @author chenyuzhe <chenyuzhe01@baidu.com>
 * @since 2012-11-06
 * @package DB
 *
 */

class Fis_DB_SplitDB
{
    const MAX_SQL_SPLIT_COUNT = 1024;
    
    const SPLITDB_SQL_SELECT = 1;
	const SPLITDB_SQL_UPDATE = 2;
	const SPLITDB_SQL_INSERT = 3;
	const SPLITDB_SQL_DELETE = 4;
	const SPLITDB_SQL_REPLACE = 5;
	
	const SPLITDB_SPLIT_TYPE_SINGLE = 1;
	const SPLITDB_SPLIT_TYPE_RANGE = 2;
	const SPLITDB_SPLIT_TYPE_MOD = 3;
	
	const MYSQL_ERR_TABLE_NOT_EXIST = 1146;
	
	//Error
	public static $COMMON_ERRNO = 1000;
	public static $CONNECT_ERRNO = 1001;
	public static $QUERY_ERRNO = 1002;
	public static $TRANSFORM_ERRNO = 1003;

	
	public static $COMMON_ERROR = 'common error';
	public static $CONNECT_ERROR = 'mysql connect error';
	public static $QUERY_ERROR = 'query error';
	public static $TRANSFORM_ERROR = 'transform error';

	
	//Conf
	private $_strConfPath;
	private $_strConfFilename;
	
	//DB attribute
	private $_strDBName;
	private $_strCharset;
	private $_arrMysql;
	
	//Query attribute
	private $_bolIsInTransaction;
	private $_intLastDBNum;
	private $_intUseConnectionNum;
	private $_intAffectedRows;
	private $_arrQueryError;
	private $_bolIsSqlTransformError;
	
	//Timer
	private $_timer;
	private $_connectTime;
	private $_queryTime;
	private $_transTime;
	
	//log info
	private $_strLastSQL;
	
	//hook
	const HK_BEFORE_QUERY = 0;
	const HK_AFTER_QUERY = 1;

	private $hkBeforeQ = array();
	private $hkAfterQ = array();
	private $onfail = NULL;
	

	public function __construct($strDBName,$strConfPath,$strConfFilename)
	{
		$this->_strDBName = $strDBName;
		$this->_strConfPath = $strConfPath;
		$this->_strConfFilename = $strConfFilename;
		$this->_arrMysql = array();
		$this->_bolIsInTransaction = false;
		$this->reset();
		$this->_timer = new Fis_Timer(false,Fis_Timer::PRECISION_US);
		$this->_connectTime = 0;
		$this->_queryTime = 0;
		$this->_transTime = 0;
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	private function reset()
	{
		$this->_intLastDBNum = -1;
		$this->_intUseConnectionNum = 0;
		$this->_intAffectedRows = 0;
		$this->_arrQueryError = array();
		$this->_bolIsSqlTransformError = false;
	}
	
	private function connect($strName)
	{
		$ret = ral_get_service($strName);
				
		$intServerNum = array_rand($ret['server']);
		
		$server = $ret['server'][$intServerNum];
		$dbConf = array(
			'host' => $server['ip'],
			'port' => $server['port'],
			'uname' => $ret['user'],
			'passwd' => base64_decode($ret['passwd']),
			'flags' => 0,
			'dbname' => $ret['extra']['dbname'],
		);
		$mysql = mysqli_init();
		
		$this->_timer->start();
		$ret = $mysql->real_connect($dbConf['host'],$dbConf['uname'],$dbConf['passwd'],$dbConf['dbname'],$dbConf['port'],NULL,$dbConf['flags']);
		$time = $this->_timer->stop();
		$this->_connectTime += $time;
		
		$this->_arrMysql[$strName] = $mysql;
		if(!$ret)
		{
			Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $strName, "connect", "$host:$port", 0, 0, 0, 0, 0, $dbConf['dbname'], "", $mysql->errno, $mysql->error);
			return false;
		}
		
		//set charset while connect
		if($this->_strCharset)
		{
			$mysql->set_charset($this->_strCharset);
		}
			
		return true;
	}
	
	/**
	* @brief 关闭连接
	*
	* @return 
	*/
	public function close()
	{	
		foreach ($this->_arrMysql as $mysql){
			$mysql->close();
		}
		
		$this->_arrMysql = array();
	}
		    
    public function doSql($strSql,$fetchType = Fis_Db::FETCH_ASSOC,$bolUseResult = false)
    {
    	// check the env before do sql query;
    	//is in transaction is set outer;
    	if($this->_bolIsInTransaction)
    	{
    		if($this->_intUseConnectionNum > 1)
    		{
    			return false;
    		}
    		$this->_intAffectedRows = 0;
    		$this->_arrQueryError = array();
    		$this->_bolIsSqlTransformError = false;
    	}
    	else
    	{
    		//clear the last query;
    		$this->reset();
    	}
    	$this->_timer->start();
        $arrSql = transform($this->_strDBName,Bingo_Encode::convert($strSql,Bingo_Encode::ENCODE_UTF8,Bingo_Encode::ENCODE_GBK),$this->_strConfPath,$this->_strConfFilename);
        $this->_transTime += $this->_timer->stop();
        if(NULL === $arrSql || $arrSql['err_no']!==0)
        {
        	$this->_bolIsSqlTransformError = true;
        	Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'transform', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        		0, 0, 0, $this->_transTime, '', $strSql, self::$TRANSFORM_ERRNO, self::$TRANSFORM_ERROR);
            return false;
        }
        //some check before conneciton;
        if(Fis_DB_SplitDB::SPLITDB_SQL_SELECT === $arrSql['sql_type'] && $bolUseResult ===true)
        {
        	Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'check', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        		0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "select should be store result");
            return false;            
        }
        $arrSplitIndex = $this->analyseSqlTransResult($arrSql,Fis_DB_SplitDB::MAX_SQL_SPLIT_COUNT);
        if( !$arrSplitIndex || count($arrSplitIndex) <= 0)
        {
        	Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'check', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        		0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "splitindex count error");
            return false;
        }
        
        //open the connection;
        if($this->_bolIsInTransaction)
        {
	        // in transaction, only 1 split is ok;
	    	if(count($arrSplitIndex) !== 1)
	    	{
	    		Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'check', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
	    			0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "in transaction split count should be 1");
	    		return false;
	    	}
	    	//in transaction , the new sql split index should be the same as the last one;
	    	if($this->_intUseConnectionNum === 1)
	    	{
	    		if($arrSplitIndex[0] !== $this->_intLastDBNum)
	    		{
	    			Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'check', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
	    				0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "in transaction connect should be the same");
	    			return false;
	    		}
	    	}
	    	else
	    	{	    		
	    		if($arrSplitIndex[0] === -1)
	    		{
	    			$strDBInstanceName = $this->_strDBName;
	    		}
	    		else
	    		{
	    			$strDBInstanceName = $this->_strDBName.$arrSplitIndex[0];
	    		}
	    		 
	    		if(is_null($this->_arrMysql[$strDBInstanceName]))
	        	{
	        		$ret = $this->connect($strDBInstanceName);
	        		if(!$ret)
	        		{
	        			$this->_arrQueryError[ $strDBInstanceName ] = '';
	        			return false;
	        		}
	        	}
	        	else
	        	{
	        		//echo "connection exist!\n";
	        	}
	    	
	        	$this->_arrMysql[$strDBInstanceName]->query('START TRANSACTION');
	    		$this->_intLastDBNum = $arrSql['sqls'][0]['splitdb_index'];
	    		$this->_intUseConnectionNum = 1;
	    	}
        }
        else
        {
	        foreach($arrSplitIndex as $intSplitIndex)
	        {        	
	        	if($intSplitIndex === -1)
	        	{
	        		$strDBInstanceName = $this->_strDBName;
	        	}
	        	else
	        	{
	        		$strDBInstanceName = $this->_strDBName.$intSplitIndex;
	        	}
	        	 
	        	if(is_null($this->_arrMysql[$strDBInstanceName]))
	        	{
	        		$ret = $this->connect($strDBInstanceName);
	        		if(!$ret)
	        		{
	        			$this->_arrQueryError[ $strDBInstanceName ] = '';
						break;
	        		}
	        	}
	        	else
	        	{
	        		//echo "connection exist!\n";
	        	}
	        	$this->_intUseConnectionNum ++;
	        }
	        if($this->_intUseConnectionNum !== count($arrSplitIndex))
	        {
	        	$this->_intUseConnectionNum = 0;
	        	return false;
	        }    
	        if($this->_intUseConnectionNum === 1)
	        {
	        	$this->_intLastDBNum = $arrSplitIndex[0];
	        }
        }
        
        //build result
        switch($fetchType)
        {
        	case Fis_Db::FETCH_OBJ:
        		$ret = new Fis_Db_SplitDBResult();
        		break;
        		 
        	case Fis_Db::FETCH_ASSOC:
        		$ret = array();
        		break;
        		 
        	case Fis_Db::FETCH_ROW:
        		$ret = array();
        		break;
        		 
        	default:
        		Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'check', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        			0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "fetch type error");
        		return false;
        }
        
        // execute hooks before query
        foreach($this->hkBeforeQ as $arrCallback)
        {
        	$func = $arrCallback[0];
        	$extArgs = $arrCallback[1];
        	if(call_user_func_array($func, array($this, &$sql, $extArgs)) === false)
        	{
        		Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'query', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        			0, 0, 0, 0, '', $strSql, self::$COMMON_ERRNO, "execute hooks before query fail");
        		return false;
        	}
        }
        
        //do sql query

        //at least one success
        $bolAtLeastOneSuccess = false;
        
        foreach ($arrSql['sqls'] as $oneSql)
        {
        	if($oneSql['splitdb_index'] === -1)
        	{
        		$strDBInstanceName = $this->_strDBName;
        	}
        	else 
        	{
        		$strDBInstanceName = $this->_strDBName.$oneSql['splitdb_index'];
        	}
        	
        	$this->_timer->start();
        	$res = $this->_arrMysql[ $strDBInstanceName ]->query(Bingo_Encode::convert($oneSql['sql'],Bingo_Encode::ENCODE_GBK,Bingo_Encode::ENCODE_UTF8),$bolUseResult?MYSQLI_USE_RESULT:MYSQLI_STORE_RESULT);
        	$time = $this->_timer->stop();
        	$this->_queryTime += $time;
        	
        	if(is_bool($res) || $res === NULL)
        	{
        		$ok = ($res == true);
        		// call fail handler
        		if(!$ok)
        		{
        			//echo "query fail {$oneSql['sql']}\n";
        			
        			if ( $arrSql['sql_type'] === Fis_DB_SplitDB::SPLITDB_SQL_SELECT )
        			{
        				if( is_null( $this->_arrQueryError[ $strDBInstanceName ] ) )
        				{
        					$this->_arrQueryError[ $strDBInstanceName ] = $oneSql['sql'];
        				}
        				continue;
        			}
        			else if ( ($arrSql['sql_type'] === Fis_DB_SplitDB::SPLITDB_SQL_UPDATE || $arrSql['sql_type'] === Fis_DB_SplitDB::SPLITDB_SQL_DELETE) &&
        					$this->_arrMysql[ $strDBInstanceName ]->errno === Fis_DB_SplitDB::MYSQL_ERR_TABLE_NOT_EXIST &&
        					$arrSql['table_split_type'] === Fis_DB_SplitDB::SPLITDB_SPLIT_TYPE_RANGE )
        			{
        				//echo "delete or update fail!\n";
        				continue;	
        			}
        			else
        			{
        				if( is_null( $this->_arrQueryError[ $strDBInstanceName ] ) )
        				{
        					$this->_arrQueryError[ $strDBInstanceName ] = $oneSql['sql'];
        				}
        				Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'query', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        					 0, round($time/1000), 0, 0, $strDBInstanceName, $oneSql['sql'], self::$QUERY_ERRNO, self::$QUERY_ERROR);
        				
        				if($this->onfail !== NULL){
        					call_user_func_array($this->onfail, array($this, &$ok));
        				}
        				
        				return false;
        			}
        		}
        		else
        		{
        			//commit query success
        			if($this->_arrMysql[ $strDBInstanceName ]->affected_rows>=0)
        			{
        				$this->_intAffectedRows +=  $this->_arrMysql[ $strDBInstanceName ]->affected_rows;
        			}
        		}
        		
        	}
        	else
        	{
        		//mysqli_result object returned
        		if($arrSql['sql_type'] === Fis_DB_SplitDB::SPLITDB_SQL_SELECT)
        		{
        			//add to result
        			switch($fetchType)
        			{
        				case Fis_Db::FETCH_OBJ:
        					$ret->addresult($res);
        					break;
        			
        				case Fis_Db::FETCH_ASSOC:
        					while($row = $res->fetch_assoc())
        					{
        						$ret[] = $row;
        					}
        					$res->free();
        					break;
        			
        				case Fis_Db::FETCH_ROW:
        					while($row = $res->fetch_row())
        					{
        						$ret[] = $row;
        					}
        					$res->free();
        					break;
        			
        				default:
        					return false;
        			}
        			        
        			//echo "query success ".$oneSql['sql']."\n";
        			        
        			$bolAtLeastOneSuccess = true;
        			$this->_intAffectedRows = count($ret);
        		}
        	}
        }
        
        if($arrSql['sql_type'] === Fis_DB_SplitDB::SPLITDB_SQL_SELECT && !$bolAtLeastOneSuccess ){
        	Fis_Db_RALLog::warning(RAL_LOG_SUM_FAIL, "SplitDB", $this->_strDBName, 'query', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        		0, round($this->_queryTime/1000), 0, 0, '', $strSql, self::$QUERY_ERRNO, self::$QUERY_ERROR);
        	
        	if($this->onfail !== NULL){
        		call_user_func_array($this->onfail, array($this, &$ret));
        	}
        	
        	return false;
        }
        
        // execute hooks after query
        foreach($this->hkAfterQ as $arrCallback)
        {
        	$func = $arrCallback[0];
        	$extArgs = $arrCallback[1];
        	call_user_func_array($func, array($this, &$ret, $extArgs));
        }
                
        Fis_Db_RALLog::notice(RAL_LOG_SUM_SUCC, "SplitDB", $this->_strDBName, 'query', '', $this->_timer->getTotalTime(Fis_Timer::PRECISION_MS),
        	round($this->_connectTime/1000), round($this->_queryTime/1000), 0, round($this->_transTime/1000), '', $strSql,count($arrSql['sqls']));
            	
        if( $arrSql['sql_type'] !== Fis_DB_SplitDB::SPLITDB_SQL_SELECT){
        	$ret = true;
        }
        return $ret;
    }
        
    private function analyseSqlTransResult($arrSql,$intMaxCount)
    {
        if(is_null($arrSql) || count($arrSql)===0)
        {
            return false;
        }
        $arrSplitIndex = array();
        foreach($arrSql['sqls'] as $strSql)
        {
            if( !in_array($strSql['splitdb_index'],$arrSplitIndex,true) )
            {
                $arrSplitIndex[] = intval($strSql['splitdb_index']);
            }
        }
        if(count($arrSplitIndex)>$intMaxCount)
        {
            return false;
        }
        return $arrSplitIndex;
    }
    
    
    public function charset($name = NULL)
    {
    	if($name === NULL)
    	{
    		foreach ($this->_arrMysql as $mysql)
    		{
    			return $mysql->character_set_name();
    		}
    		return false;
    	}
    	$this->_strCharset = $name;
    	return true;
    }	
    
    /**
	* @brief 开始事务
	*
	* @return 
	*/
    public function startTransaction()
    {
    	if($this->_bolIsInTransaction)
    	{
    		$this->commit();
    	}
    	$this->reset();
    	$this->_bolIsInTransaction = true;
    	
    	return true;
    }
    
    /**
	* @brief 提交事务
	*
	* @return 
	*/
    public function commit()
    {
    	if(!$this->_bolIsInTransaction)
    	{
    		return false;
    	}
    	if($this->_intUseConnectionNum !== 1)
    	{
    		return false;
    	}
    	
    	if($this->_intLastDBNum === -1)
    	{
    		$strDBInstanceName = $this->_strDBName;
    	}
    	else
    	{
    		$strDBInstanceName = $this->_strDBName.$this->_intLastDBNum;
    	}
    	
    	$this->_arrMysql[$strDBInstanceName]->commit();
    	$this->_bolIsInTransaction = false;
    	 
    	return true;
    }
    
    /**
	* @brief 回滚
	*
	* @return 
	*/
    public function rollback()
    {
    	if(!$this->_bolIsInTransaction)
    	{
    		return false;
    	}
    	if($this->_intUseConnectionNum !== 1)
    	{
    		return false;
    	}
    	 
    	if($this->_intLastDBNum === -1)
    	{
    		$strDBInstanceName = $this->_strDBName;
    	}
    	else
    	{
    		$strDBInstanceName = $this->_strDBName.$this->_intLastDBNum;
    	}
    	
    	$this->_arrMysql[$strDBInstanceName]->rollback();
    	$this->_bolIsInTransaction = false;
    
    	return true;
    }
    
    public function getLastSQL()
    {
    	return $this->_strLastSQL;
    }
    
    /**
	* @brief 获取受影响的行数
	*
	* @return 
	*/
    public function getAffectedRows()
    {   
    	return $this->_intAffectedRows;
    }
    
    /**
	* @brief 获取Insert_id
	*
	* @return 
	*/
    public function getInsertID()
    {
    	if($this->_intUseConnectionNum !== 1)
    	{
    		return false;
    	}
    	
    	if($this->_intLastDBNum === -1)
    	{
    		$strDBInstanceName = $this->_strDBName;
    	}
    	else
    	{
    		$strDBInstanceName = $this->_strDBName.$this->_intLastDBNum;
    	}
    	
    	return $this->_arrMysql[$strDBInstanceName]->insert_id;
    }
    
    /**
	* @brief 获取当前mysqli错误码
	*
	* @return 
	*/
    public function getMysqlErrno()
    {
    	if($this->_bolIsSqlTransformError)
    	{
    		return 9990;
    	}
    	foreach ($this->_arrQueryError as $strDBInstanceName => $strSql)
    	{
    		return $this->_arrMysql[$strDBInstanceName]->errno;
    	}
    	return 0;	
    }
    
    /**
	* @brief 获取当前mysqli错误描述
	*
	* @return 
	*/
    public function getMysqlError()
    {
    	if($this->_bolIsSqlTransformError)
    	{
    		return "sql transform fail";
    	}
    	foreach ($this->_arrQueryError as $strDBInstanceName => $strSql)
    	{
    		return $this->_arrMysql[$strDBInstanceName]->error;
    	}
    	return "success";
    }
    
    /**
	* @brief 基于当前连接的字符集escape字符串
	*
	* @param $string 输入字符串
	*
	* @return 
	*/
	public function escapeString($string)
    {
    	if(count($this->_arrMysql) === 0)
    	{   		
    		$conf = Fis_Conf::getConf('/db/'.basename($this->_strConfFilename,'.conf'));
    		if($conf == false){
    			return false;
    		}
    		
    		$intDBCount = intval($conf['DBSplitPattern'][$this->_strDBName]['DBCount']);
    		
    		$intCurDB = rand(0,$intDBCount-1);
    		
    		//如果没有配置或只有1个数据库
    		if($intDBCount === 1 || $intDBCount === 0)
    		{
    			$strDBInstanceName = $this->_strDBName;
    		}
    		else
    		{
    			$strDBInstanceName = $this->_strDBName.$intCurDB;
    		}
    		
    		if(is_null($this->_arrMysql[$strDBInstanceName]))
    		{
    			$ret = $this->connect($strDBInstanceName);
    			if(!$ret)
    			{
    				return false;
    			}
    		}
    		else
    		{
    			//echo "connection exist!\n";
    		}
    		
    		return $this->_arrMysql[$strDBInstanceName]->real_escape_string($string);
    	}
    	
    	foreach ($this->_arrMysql as $mysql)
    	{
    		return $mysql->real_escape_string($string);
    	}
    	//if connection not exist
    	
    }
    
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
    	switch($where)
    	{
    		case self::HK_BEFORE_QUERY:
    			$dest = &$this->hkBeforeQ;
    			break;
    		case self::HK_AFTER_QUERY:
    			$dest = &$this->hkAfterQ;
    			break;
    		default:
    			return false;
    	}
    	if(!is_callable($func))
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
    	if($func === 0)
    	{
    		return $this->onfail;
    	}
    	if($func === NULL)
    	{
    		$this->onfail = NULL;
    		return true;
    	}
    	if(!is_callable($func))
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
    	switch($where)
    	{
    		case self::HK_BEFORE_QUERY:
    			$dest = &$this->hkBeforeQ;
    			break;
    		case self::HK_AFTER_QUERY:
    			$dest = &$this->hkAfterQ;
    			break;
    		default:
    			return false;
    	}
    	if(!array_key_exists($id, $dest))
    	{
    		return false;
    	}
    	unset($dest[$id]);
    	return true;
    }
}