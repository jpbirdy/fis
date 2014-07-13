<?php

/**
 * @file IdAllocWrapper.php
 * @author liaohuiqin01
 * @date 2013/05/13 20:34:40
 * @brief ksarch的idalloc基础封装类
 */


class SF_Wrapper_KSArch_IdAlloc extends SF_Wrapper_KSArch_Wrapper{
	/**
	 * $pid (string)产品线ID
	 * $tk  (string)切分tk
	 * $uid (int)uid
	 *
	 * 创建IdAlloc服务接口
	 */
	public function __construct($params = null){
		try{
			parent::__construct($params);
			$this->_pid = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/idalloc/pid");
			if(empty($pid)){
				$pid = SF_Wrapper_KSArch_KsarchFactory::KS_ARCH_PID;
			}
			$tk   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/idalloc/tk");
	    	if(empty($tk)){
				$tk = '*';
			}
			$uid   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/idalloc/uid");
	    	if(empty($uid)){
				$uid = 1;
			}else{
				$uid = intval($uid);
			}
	
			$this->idalloc = Bd_RalRpc::create(
				'IdAlloc',
				array(
						'pid'=>$this->_pid, 
						'tk'=>$tk, 
						'uid'=>$uid
				)
			);
			$timeUsed = $this->stop();
			if( empty($this->idalloc) ) {
				Bd_Log::warning("KSARCH_MONITOR_IdAlloc create failed", Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				Bd_Log::trace("KSARCH_MONITOR_IdAlloc construct used($timeUsed)us  [failed]");
			}
		
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_IdAlloc create catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
		Bd_Log::trace("KSARCH_MONITOR_IdAlloc construct used($timeUsed)us [OK]");
	}

    /**
     *
     * 返回自增ID
     *
     * @param string $idalloc_name
     * @throws Exception
     * @return array
     */
	public function nextval($idalloc_name){
		$this->start();
		if(empty($this->idalloc)){
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) exception instance is null",
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			throw new Exception("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) exception instance is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
		}

        if(empty($idalloc_name)){
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc nextval empty idalloc_name",Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
            throw new Exception("KSARCH_MONITOR_IdAlloc nextval name is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
        }
		$res = $this->idalloc->INCR(
			array('name' => $idalloc_name, 'method' => 'default')
		);
		$res_logstr = json_encode($res);
		$timeUsed = $this->stop();
		if( empty($res) || !is_array($res) || !isset($res['error']) ) {
			//返回的格式不对;
			Bd_Log::warning('KSARCH_MONITOR_IdAlloc INCR idalloc_name('.$idalloc_name.') res('.
				$res_logstr.') is empty or not array', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			throw new Exception('IdAlloc talk to idalloc failed exception');
		}else if( $res['error']!==0 || empty($res['id']) ){
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) res($res_logstr) failed!");
			throw new Exception("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) exception");
		}else{
			Bd_Log::trace("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) id(".$res['id'].") use($timeUsed)us [OK]");
			return array('err_no'=>$res['error'],'id'=>$res['id']);
		}
		//Bd_Log::trace("KSARCH_MONITOR_IdAlloc nextval($idalloc_name) id(".$res['id'].") use($timeUsed)us [OK]");
	}
	/**
	 * 
	 * 以step的步长来自增ID
	 * 
	 * @param string $idalloc_name
	 * @return array 
	 */
	public function stepval($idalloc_name,$step=10){
		$this->start();
		try{
			if(empty($this->idalloc)){
				Bd_Log::warning("KSARCH_MONITOR_IdAlloc stepval($idalloc_name) exception instance is null",
					Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
				throw new Exception("KSARCH_MONITOR_IdAlloc stepval($idalloc_name) exception instance is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			}
			if(empty($idalloc_name)){
				Bd_Log::warning("KSARCH_MONITOR_IdAlloc stepval empty idalloc_name",Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
				throw new Exception("KSARCH_MONITOR_IdAlloc stepval($idalloc_name) exception KSARCH_MONITOR_IdAlloc name is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			}

			$res = $this->idalloc->INCR_STEP(
				array('name' => $idalloc_name, 'step'=>$step, 'method' => 'default')
			);

			$timeUsed = $this->stop();
			$pid_name = trim($this->_pid) . "_" . trim($idalloc_name);

			$res_logstr = json_encode($res);
			if( empty($res) || !is_array($res) || !isset($res['error']) ) {
				//返回的格式不对;
				Bd_Log::warning('KSARCH_MONITOR_IdAlloc INCR_STEP idalloc_name('.
				   $idalloc_name.'), step('.$step.')	but res('.
				   $res_logstr.') is empty or not array', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				throw new Exception('IdAlloc talk to idalloc failed exception');
			}else if($res['error']!==0 || empty($res[$pid_name]) ){
				Bd_Log::warning("KSARCH_MONITOR_IdAlloc stepval($idalloc_name) res($res_logstr) failed!",
					Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				throw new Exception("KSARCH_MONITOR_IdAlloc stepval($idalloc_name) exception");
			}else{
				return array('err_no' => $res['error'],'ids' => $res[$pid_name]);
			}
		}catch(Exception $e) {
			Bd_Log::warning('KSARCH_MONITOR_IdAlloc stepval catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
	}

    /**
     *
     * 返回当前ID
     *
     * @param string $idalloc_name
     * @throws Exception
     * @return array
     */
	public function currval($idalloc_name){
		$this->start();
		if(empty($this->idalloc)){
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc currval($idalloc_name) exception instance is null",
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			throw new Exception("KSARCH_MONITOR_IdAlloc currval($idalloc_name) exception instance is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
		}
        if(empty($idalloc_name)){
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc currval empty idalloc_name",Tuanbai_Constant_SysErr::SYS_ERR_PARAM);
            throw new Exception("KSARCH_MONITOR_IdAlloc currval($idalloc_name) exception KSARCH_MONITOR_IdAlloc name is null",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
        }
		$res = $this->idalloc->GET(
			array('name' => $idalloc_name)
		);
		$res_logstr = json_encode($res);
		$timeUsed = $this->stop();
		if( empty($res) || !is_array($res) || !isset($res['error']) ) {
			//返回的格式不对;
			Bd_Log::warning('KSARCH_MONITOR_IdAlloc GET currval('.$idalloc_name.') res('.
				$res_logstr.') is empty or not array', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			throw new Exception('IdAlloc talk to idalloc GET failed exception');
		}else if( $res['error']!==0 || empty($res['id']) ) {
			Bd_Log::warning("KSARCH_MONITOR_IdAlloc GET currval($idalloc_name) res($res_logstr) failed!",
				Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			throw new Exception("KSARCH_MONITOR_IdAlloc currval($idalloc_name) exception");
		}else{
			Bd_Log::trace("KSARCH_MONITOR_IdAlloc currval($idalloc_name) id(".$res['id'].") used($timeUsed)us [OK]");
			return array('err_no'=>$res['error'],'id'=>$res['id']);
		}
		//Bd_Log::trace("KSARCH_MONITOR_IdAlloc currval($idalloc_name) id(".$res['id'].") used($timeUsed)us [OK]");
	}
}