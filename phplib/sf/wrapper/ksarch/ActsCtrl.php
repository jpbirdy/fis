<?php
/**
 * @file ActsCtrlWrapperKSArchActsCtrl.php
 * @author liaohuiqin01
 * @date 2013/05/11 16:35:49
 * @brief ksarch ActsCtrl接口基础封装类;
 */

class SF_Wrapper_KSArch_ActsCtrl extends SF_Wrapper_KSArch_Wrapper{
	private $actsCtrl = null;
	/**
	 * $pid (string) 产品线ID 向ksarch申请
	 * $tk  (string) 切分tk  需要向ksarch申请
	 * type (string) 可选为 query 或 submit
	 *
	 * 创建ActsCtrl服务接口
	 */
	public function __construct($params = null){
		parent::__construct($params);
		$pid   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/actsctrl/pid");
		if(empty($pid)){
			$pid = SF_Wrapper_KSArch_KsarchFactory::KS_ARCH_PID;
		}
		$tk = SF_Wrapper_KSArch_KsarchFactory::KS_ARCH_PID;
		if(empty($params)){
			$type = 'submit';
		}else{
			$type = $params['type'];
		}
		$this->actsCtrl = Bd_RalRpc::create(
			'ActsCtrl',
		array(
				'pid'=>$pid, 
				'tk'=>$tk,
				'type'=>$type
		)
		);
		$timeUsed = $this->stop();

		if( empty($this->actsCtrl) ) {
			Bd_Log::warning("KSARCH_MONITOR_ActsCtrl create failed", Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR); 
			Bd_Log::trace("KSARCH_MONITOR_ActsCtrl construct used($timeUsed)us [failed]");
		}else{
			Bd_Log::trace("KSARCH_MONITOR_ActsCtrl construct used($timeUsed)us [OK]");
		}
	}
	/**
	 *
	 * @param array $para
	 * @return unknown_type
	 */
	public function actsctrl($para){

		$log_str = json_encode($para); //打印日志使用;
		if(empty($this->actsCtrl)){
			//未初始化;
			$res['err_no'] = Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR;
			$res['err_msg'] = 'ActsCtrl service instance is null, not initialized!';
			Bd_Log::warning('KSARCH_MONITOR_ActsCtrl actsctrl('.var_export($para,true).') instance is null!',
				Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return $res;
		}
		$this->start();
		try{
			$res = $this->actsCtrl->actsctrl($para);
			$res_logstr = json_encode($res); //打印日志使用；
			if( empty($res) || !is_array($res) || !isset($res['err_no']) ) {
				//返回的格式不对;
				Bd_Log::warning('KSARCH_MONITOR_ActsCtrl actsctrl('.$log_str.') but res('.
				   $res_logstr.') is empty or not array', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				throw new Exception('ActsCtrl talk to actsctrl failed exception');
			}else if( $res['err_no'] != 0 ){
				//返回的格式正确，但有错误码;
				Bd_Log::warning('KSARCH_MONITOR_ActsCtrl request('.$log_str.
					') res('.$res_logstr.') failed!', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				throw new Exception('ActsCtrl talk to actsctrl failed exception');
			}else{
				Bd_Log::trace('KSARCH_MONITOR_ActsCtrl actsctrl('.$log_str.')'.' res('.$res_logstr.') success');
				return array('err_no'=>$res['err_no'],'result'=>$res['result']);
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_ActsCtrl catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
		//$timeUsed = $this->stop();
		//Bd_Log::trace("KSARCH_MONITOR_ActsCtrl talk to actsctrl used($timeUsed)us ");
	}
}
