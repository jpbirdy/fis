<?php
/**
 * @file Cache.php
 * @author liaohuiqin01
 * @date 2013/05/13 10:24:39
 * @brief ksarch的memcache 接口基础封装;
 */


class Cache extends SF_Wrapper_KSArch_Wrapper{
	const CACHE_PREFIX = 'lbs_gpw_';
	const CACHE_OPEN = 1;
	const CACHE_CLOSED = 0;
	const CACHE_WRITE_ONLY = 2;

	private $switch = Tuanbai_Conf_GlobalConfig::SELFTG_MEMCACHE_SWITCH; //1 开启缓存 0 关闭缓存 2 只写不读?
	private $cache = null;
	private $iExpire = Tuanbai_Constant_Constant::CACHE_EXPIRE_DEFAULT;//默认自动失效时间;
	
	public function openWriteOnly(){
		$this->switch = self::CACHE_WRITE_ONLY;
	}
	public function closed(){
		$this->switch = self::CACHE_CLOSED;
	}
	public function open() {
		$this->switch = self::CACHE_OPEN;
	}
	/**
	 * $pid (string)产品线ID
	 * $tk  (string)切分tk
	 * $uid (int)uid
	 *
	 * 创建Memecache实例;
	 * @ret  异常返回null, 正常返回对象实例;
	 */
	public function __construct($params = null){
		try{
			parent::__construct($params);
			$this->start(); //开始计时;

			if(in_array($this->switch,array(self::CACHE_OPEN,self::CACHE_WRITE_ONLY))){
				$arrHost   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/memcached/server");
				$strZKPath = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/memcached/zkpath");
				$iCurrIdc  = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/memcached/curr_idc");
				$prefix = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/memcached/prefix");
				$pid = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/memcached/pid");
				$iCurrIdc = intval($iCurrIdc);
				if(empty($params)){
					$tk = '*';
					$uid = 0;
				}else{
					$tk = $params['tk'];
					$uid = intval($params['uid']);
				}
				if(empty($prefix)){
					$this->prefix = $prefix;
				}
				if(!empty($strPid)){
					$pid = $strPid;
				}

				Ak_Zookeeper::setHost($arrHost);
				$arrMCConf = array(
					'pid'=>$pid, 
					'zk_path'=>$strZKPath, 
					'curr_idc'=>$iCurrIdc,
					'zk_expire'=>60
				);
				$this->cache = Ak_McClient::create($arrMCConf);
				$timeUsed = $this->stop();
				if( empty($this->cache) ) {
					Bd_Log::warning("KSARCH_MONITOR_Cache create failed", Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
					Bd_Log::trace("KSARCH_MONITOR_Cache construct used($timeUsed)us  [failed]");
					return null;
				}
			}else{
				Bd_Log::trace("KSARCH_MONITOR_Cache switch(".$this->switch.") is not open, will not create instance!");
				return null;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache create catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
		Bd_Log::trace("KSARCH_MONITOR Cache construct use($timeUsed)us  [OK]");
	}



	/**
	 * @brief 设置cache;
	 * @param key: hash key
	 *        oValue: 要设置的值;
	 *        iExpire: 自动失效时间(s)
	 *        bKeyMd5: 是否要将key进行md5签名
	 * @ret   异常返回null，其余返回true;
	 **/
	public function set($strKey, $oValue, $iExpire = null, $bKeyMd5 = true) {
		if( empty($this->cache) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache set key('.$strKey.') but has not initialized cache instance',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return null;
		}

		if( empty($strKey) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache set key is empty', Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
			return null;
		}
		//oValue可以为空;

		//检测cache 开关；
		if( !in_array($this->switch, array(self::CACHE_OPEN,self::CACHE_WRITE_ONLY)) ) {
			Bd_Log::trace('KSARCH_MONITOR_Cache find cache switch('.
				$this->switch.') not open cache, set key('.$strKey.') will jump cache set request....');
			return null;
		}

		//默认失效时间;
		if(empty($iExpire)){
			$iExpire = $this->iExpire ;
		}
		$strSetKey = $this->genKey($strKey, self::CACHE_PREFIX, $bKeyMd5);
		try{
			$this->start(); //计时开始;
			$ret = $this->cache->set($strSetKey, $oValue, $iExpire);
			$timeUsed = $this->stop(); //计时结束;
			if ($ret) {
				Bd_Log::trace("KSARCH_MONITOR_Cache set key($strKey) use($timeUsed)us [OK] ");
				return $ret;
			} else {
				Bd_Log::trace("KSARCH_MONITOR_Cache set key($strKey) use($timeUsed)us [failed] ");
				return null;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache set key('.$strKey.') catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
	}

/**
	 * @brief 删除cache;
	 * @param key: hash key
	 *        timeOut: 删除延迟时间(s)
	 *        bKeyMd5: 是否要将key进行md5签名
	 * @ret   异常返回null，其余返回true;
	 **/
	public function delete($strKey, $timeOut = 0, $bKeyMd5 = true) {
		if( empty($this->cache) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache delete key('.$strKey.') but has not initialized cache instance',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return false;
		}

		if( empty($strKey) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache delete key is empty', Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
			return false;
		}
	
		//检测cache 开关；
		if( !in_array($this->switch, array(self::CACHE_OPEN,self::CACHE_WRITE_ONLY)) ) {
			Bd_Log::trace('KSARCH_MONITOR_Cache find cache switch('.
				$this->switch.') not open cache, delete key('.$strKey.') will jump cache set request....');
			return false;
		}

		$strSetKey = $this->genKey($strKey, self::CACHE_PREFIX, $bKeyMd5);
		try{
			$this->start(); //计时开始;
			$ret = $this->cache->delete($strSetKey, $timeOut);
			$timeUsed = $this->stop(); //计时结束;
			if ($ret) {
				Bd_Log::trace("KSARCH_MONITOR_Cache delete key($strKey) use($timeUsed)us [OK] ");
				return $ret;
			} else {
				Bd_Log::trace("KSARCH_MONITOR_Cache delete key($strKey) use($timeUsed)us [failed] ");
				return false;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache delete key('.$strKey.') catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return false;
		}
	}

	/**
	 * @brief 从cache中读;
	 * @param key: hash key
	 *        bKeyMd5: 是否要将key进行md5签名
	 * @ret   异常返回null，其余返回命中的字符串;
	 **/
	public function get($strKey, $bKeyMd5 = true) {
		//先检测cache对象;
		if( empty($this->cache) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache set key('.$strKey.') but has not initialized cache instance',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return null;
		}

		//不允许空key;
		if( empty($strKey) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache set key is empty', Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
			return null;
		}

		//检测cache开关;
		if( $this->switch != self::CACHE_OPEN ) {
			Bd_Log::trace('KSARCH_MONITOR_Cache find cache switch('.
				$this->switch.') not open cache, key('.$strKey.') will return null and jump cache get request....');
			return null;
		}

		$strGetKey = $this->genKey($strKey, self::CACHE_PREFIX, $bKeyMd5);
		try{
			$this->start(); //计时开始;
			$ret = $this->cache->get($strGetKey);
			$timeUsed = $this->stop(); //计时结束;
			if ($ret) {
				Bd_Log::trace("KSARCH_MONITOR Cache get key($strKey) use($timeUsed)us [OK]");
				return $ret;
			} else {
				//没命中;
				Bd_Log::trace("KSARCH_MONITOR Cache get key($strKey) use($timeUsed)us [NOT HIT]");
				return null;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache get key('.$strKey.') catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);

			return null;
		}
	}

	/*
	 * @brief 批量查询;
	 * @ret  返回数组，如果没有命中则为array();
	 **/
	public function getMulti($arrKeys,  $bKeyMd5 = true) {
		$log_str = serialize($arrKeys); //不使用json_encode，可能是gbk编码;
		if(empty($this->cache)){
			Bd_Log::warning('KSARCH_MONITOR_Cache getMulti but has not initialized cache instance',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return array();
		}
		//参数校验;
		if( !is_array($arrKeys) || empty($arrKeys) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache getMulti arrkeys is invalid',
				Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
			return array();
		}
		//检测cache开关;
		if( $this->switch != self::CACHE_OPEN ) {
			Bd_Log::trace('KSARCH_MONITOR_Cache find cache switch('.
				$this->switch.') not open cache, keys('.$log_str.') will return array() and jump cache get request....');
			return array();
		}

		//对key进行md5处理;
		$arrGetKeys = array();
		foreach ($arrKeys as $strKey) {
			$strGetKey = $this->genKey($strKey, self::CACHE_PREFIX, $bKeyMd5);
			$arrGetKeys[] = $strGetKey;
		}
		try{
			$this->start();
			$arrRet = $this->cache->getMulti($arrGetKeys);
			$timeUsed = $this->stop();
			if ($arrRet) {
				Bd_Log::trace("KSARCH_MONITOR_Cache getMulti keys($log_str) use($timeUsed)us [OK] ");
				return $arrRet;
			} else {
				Bd_Log::trace("KSARCH_MONITOR_Cache getMulti keys($log_str) use($timeUsed)us [FAIL] ");
				Bd_Log::warning("KSARCH_MONITOR_Cache getMulti fail");
				return array();
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache getMulti keys('.$log_str.') catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return array();
		}
	}

	/**
	 * @brief 添加cache;
	 * @ret 异常返回null; 正常返回true;
	 **/
	public function add($strKey, $oValue, $iExpire = null, $bKeyMd5 = true) {

		if( empty($this->cache) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache add key('.$strKey.') but has not initialized cache instance',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return null;
		}

		if( empty($strKey) ) {
			Bd_Log::warning('KSARCH_MONITOR_Cache add key is empty', Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
			return null;
		}
		//oValue可以为空;

		//检测cache 开关；
		if( !in_array($this->switch, array(self::CACHE_OPEN,self::CACHE_WRITE_ONLY)) ) {
			Bd_Log::trace('KSARCH_MONITOR_Cache find cache switch('.
				$this->switch.') not open cache, key('.$strKey.') will jump cache set request....');
			return null;
		}
		if(empty($iExpire)){
			$iExpire = $this->iExpire ;
		}	

		$strAddKey = $this->genKey($strKey, self::CACHE_PREFIX, $bKeyMd5);
		try{
			$this->start();
			$ret = $this->cache->add($strAddKey, $oValue, $iExpire);
			$timeUsed = $this->stop();
			if ($ret) {
				Bd_Log::trace("KSARCH_MONITOR_Cache add key($strKey) use($timeUsed)us [OK]");
				return $ret;
			} else {
				//已经存在，会失败;
				$timeUsed = $this->stop();
				Bd_Log::trace("KSARCH_MONITOR_Cache add key($strKey) use($timeUsed)us [FAIL] ");
				return $ret;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Cache add key('.$strKey.') catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
	}
	
	private function genKey($strKey, $strPrefix, $bKeyMd5) {
		if ($bKeyMd5 === true) {
			return $strPrefix . md5($strKey);
		} else {
			return $strPrefix . $strKey;
		}
	}
}
