<?php

class Fis_Db_RALLog
{
	//Error
	public static $COMMON_ERRNO = 1000;
	public static $CONNECT_ERRNO = 1001;
	public static $QUERY_ERRNO = 1002;
	public static $TRANSFORM_ERRNO = 1003;
	
	public static $COMMON_ERROR = 'common error';
	public static $CONNECT_ERROR = 'mysql connect error';
	public static $QUERY_ERROR = 'query error';
	public static $TRANSFORM_ERROR = 'transform error';
	
	
	/**
	 * @brief 打印NOTICE日志
	 *
	 * @param $logtype 日志级别
	 * @param $caller 调用者
	 * @param $service ral中的服务
	 * @param $method 方法
	 * @param $remote_ip 连接的IP
	 * @param $cost 总耗时
	 * @param $connect 连接耗时
	 * @param $read 读耗时
	 * @param $write 写耗时
	 * @param $trans 解析SQL耗时
	 * @param $dbname 数据库实例名
	 * @param $sql SQL
	 *
	 * @return
	 */
	public static function notice($logtype,$caller,$service,$method,$remote_ip,$cost,$connect,$read,$write,$trans,$dbname,$sql,$query_count)
	{
		$log = array(
				'service' => $service,
				'method' => $method,
				'prot' => 'mysql',
				'remote_ip' => $remote_ip,
				'cost' => $cost,
				'connect' => $connect,
				'read' => $read,
				'write' => $write,
				'trans' => $trans,
				'dbname' => $dbname,
				'sql' => substr($sql,0,32),
				'query_count' => $query_count,
		);
		 
		ral_write_log($logtype, $caller, $log);
	}
	
	/**
	 * @brief 打印WARNING日志
	 *
	 * @param $logtype 日志级别
	 * @param $caller 调用者
	 * @param $service ral中的服务
	 * @param $method 方法
	 * @param $remote_ip 连接的IP
	 * @param $cost 总耗时
	 * @param $connect 连接耗时
	 * @param $read 读耗时
	 * @param $write 写耗时
	 * @param $trans 解析SQL耗时
	 * @param $dbname 数据库实例名
	 * @param $sql SQL
	 * @param $err_no 错误号
	 * @param $err_info 错误信息
	 *
	 * @return
	 */
	public static function warning($logtype,$caller,$service,$method,$remote_ip,$cost,$connect,$read,$write,$trans,$dbname,$sql,$err_no,$err_info)
	{
		$log = array(
				'service' => $service,
				'method' => $method,
				'prot' => 'mysql',
				'remote_ip' => $remote_ip,
				'cost' => $cost,
				'connect' => $connect,
				'read' => $read,
				'write' => $write,
				'trans' => $trans,
				'dbname' => $dbname,
				'sql' => $sql,
				'err_no' => $err_no,
				'err_info' => $err_info,
		);
	
		ral_write_log($logtype, $caller, $log);
	}
}