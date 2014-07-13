<?php
/**
 * @file Amsg.php
 * @author liaohuiqin01
 * @date 2013/05/11 20:54:49
 * @brief ksarch的amsg 接口基础封装;
 */

class SF_Wrapper_KSArch_Amsg extends SF_Wrapper_KSArch_Wrapper{
	private $amsg = null;
	private $username = null;
	private $password = null;
	private $businesscode = null;
	/**
	 * $pid (string) 产品线ID
	 * $tk  (string) 切分tk 
	 * $uid (int)uid
	 *
	 * 创建Amsg服务实例
	 */
	public function __construct($params = null){
		parent::__construct($params);

		$pid   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/pid");
		$this->username   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/username");
		$this->password   = md5(SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/password"));
		$this->businesscode   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/businesscode");
		
		$tk   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/tk");

		if(empty($pid)){
			$pid = SF_Wrapper_KSArch_KsarchFactory::KS_ARCH_PID;
		}
		if(empty($this->username)){
			$this->username = 'test';
		}
		if(empty($this->password)){
			$this->password = md5('test');
		}
		if(empty($this->businesscode)){
			$this->businesscode = 51;
		}else{
			$this->businesscode = intval($this->businesscode);
		}
		
		if(empty($tk)){
			$tk = '*';
		}
		
		try{
			$this->amsg = Bd_RalRpc::create(
				'Amsg',
				array(
					'pid'=>$pid, 
					'tk'=>$tk
				)
			);
			
			$timeUsed = $this->stop();
			if( empty($this->amsg) ) {
				Bd_Log::warning("KSARCH_MONITOR_Amsg create failed", Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				Bd_Log::trace("KSARCH_MONITOR_Amsg construct used($timeUsed)us  [failed]");
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Amsg create catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);

			return null;

		}
		Bd_Log::trace("KSARCH_MONITOR_Amsg construct used($timeUsed)us  [OK]");
	}
	/**
	 * array(
	 * 	 'to' =>array(
	 *			'手机号', '手机号'
	 *   ),
	 *   'content' => '',
	 *   'user_name' => smsp平台的用户名，可以设置默认值;
	 *   'password' => smsp平台的密码, 可以配置默认;
	 *   'business_code' => '',//smsp平台的业务代码，可以配置默认;
	 *   'priority' => '',//短信的优先级，默认为3
	 *   'encoding' => '',//字符编码，使用gbk/utf-8，默认为gbk;
	 * )
	 *
	 * @param array $param
	 * @return unknown_type
	 */
	public function sendSmsp($to,$content,$encoding='utf-8'){
		if(empty($this->amsg)){
			$res['err_no'] = Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR;
			$res['err_msg'] = 'Amsg service instance is null!';
			Bd_Log::warning('Amsg sendSmsp but find instance is null!', Tuanbai_Constant_SysErr::SYS_ERR_NOT_INITIALIZED);
			return $res;
		}
		$timeUsed = -1; //计时信息;
		$to_logstr = json_encode($to); //日志打印;
		$this->start();

		$param = array('to'=>$to,'content'=>$content,'encoding'=>$encoding);
		$param['user_name'] = $this->username;
		$param['password'] = $this->password;
		$param['business_code'] = $this->businesscode;
		Bd_Log::debug('Sms content: ' . $content); //单独打印内容,进行调试;
		try{
			$res = $this->amsg->sendSmsp($param);
			$res_logstr = json_encode($res); //日志打印;
			$param_logstr = json_encode($param); //日志打印;
			$timeUsed = $this->stop();
			if( empty($res) || !is_array($res) || !isset($res['err_no']) ) {
				//返回的格式不对;
				Bd_Log::warning('KSARCH_MONITOR_Amsg amsg('.$param_logstr.') but res('.
				   $res_logstr.') is empty or not array', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
				throw new Exception('Amsg talk to amsg failed exception');
			}else if( $res['err_no'] != 0){
				Bd_Log::warning('KSARCH_MONITOR_Amsg sendSmsp('.$param_logstr.') but res('.
				   $res_logstr.') failed!');
				throw new Exception('Amsg sendSmsp failed ksarch res code('.$res['err_no'].')');
			}else{
				Bd_Log::trace('KSARCH_MONITOR_Amsg sendSmsp('.$to_logstr.') content('.$content.') success use('.$timeUsed.')us');
				return $res;
			}
		}catch(Exception $e){
			Bd_Log::warning('KSARCH_MONITOR_Amsg catch exception err('.$e->getMessage().') file('.
				$e->getFile().') line('.$e->getLine().')', Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			return null;
		}
		//Bd_Log::trace('KSARCH_MONITOR_Amsg sendSmsp('.$to_logstr.') content('.$content.') success use('.$timeUsed.')us');
		//return $res;
	}
}

