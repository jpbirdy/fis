<?php
/**
 * @file Mail.php
 * @author liaohuiqin01
 * @date 2013/05/14 14:33:10
 * @brief ksarch的mail基础封装类
 */



class SF_Wrapper_KSArch_Mail extends SF_Wrapper_KSArch_Wrapper{
    const SLEEP_TIME = 2;
    const times = 2;
    private $amsg;
    public function __construct() {
        
		$pid   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/pid");
		$tk   = SF_Wrapper_KSArch_ConfSF::getConf("ksarch/amsg/tk");
    	if(empty($tk)){
			$tk = '*';
		}
		$this->amsg = Bd_RalRpc::create('Amsg', array(
                            'pid' => $pid,
                            'tk' => $tk,
                      ));
        if( empty($this->amsg) ) {
            $msg = Bd_RalRpc::get_error();
			Bd_Log::warning("KSARCH_MONITOR_Mail MailServer create failed err_msg[$msg]",
				Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
			throw new Exception("KSARCH_MONITOR_Mail MailServer create failed err_msg[$msg]", Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
        }
    }
    
    /*
     *发送邮件
     *
	 *接口参数参见ksarch的mail说明; 
     */
    public function sendMail($From, $To, $subject, $content, $encoding='gbk', $times = 1) {
        Bd_Log::trace('KSARCH_MONITOR_Mail mail server start');
		if(empty($From) || empty($To) || empty($content)) {
            Bd_Log::warning(" empty From or To or Content");
            return false;
        }

        $param = array(
            'from' => $From,
            'to' => $To,
            'subject' => $subject,
            'content'  => $content,
			'encoding' => $encoding,
            );
		for($i = 0; $i < $times; $i++){
            $ret = $this->amsg->sendMail($param);
            if(0 == $ret['err_no'] || $i >= $times) {

                break;
            }
			Bd_Log::warning('KSARCH_MONITOR_Mail mail server subject['.$subject.'] end ret err_no[' . $ret['err_no'] . '] err_msg[' . $ret['err_msg'].'] retry_times['.$i.']',
			   Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
            sleep(3);

        }      
		
		if(0 != $ret['err_no']) {
			Bd_Log::warning('KSARCH_MONITOR_Mail mail server subject['.$subject.'] retry_times all failed', 
			Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
		}

        return $ret;
    }
}
