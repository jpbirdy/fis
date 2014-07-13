<?php
/**
 * @file KsarchFactory.php
 * @author liaohuiqin01
 * @date 2013/05/11 16:45:19
 * @brief ksarch服务的工厂类，根据service名创建实际接口对象;
 */

class SF_Wrapper_KSArch_KsarchFactory{
	const KS_ARCH_PID = 'lbs_groupon';
	private static $ks_instance_map = array();

	private static function createKsService($service,$params=null){

		$serviceClass = 'SF_Wrapper_KSArch_'.$service;

/*        if ($service == 'IdAlloc')//最终需要注释掉，线上使用正常的
        {
            $serviceClass = 'SF_Wrapper_KSArch_IdAllocMockSF';
        }*/

		if(!class_exists($serviceClass)){
			throw new Exception("create ksarch service[$serviceClass] class doesn't exist",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
		}
		
		if(!empty($params)&&is_array($params)){
			foreach($params as $key =>$val){
				$service .= '_'.$key.'_'.$val;
			}
		}
		$instance = new $serviceClass($params);
		if(empty($instance)){
			throw new Exception("create ksarch service[$serviceClass] instance failed",Tuanbai_Constant_SysErr::SYS_ERR_KS_ERROR);
		}
		self::$ks_instance_map[$service] = $instance;
		return $instance;
	}
	
	/**
	 * Commit
	 * Actctrl 
	 * Cache
	 * Amsg
	 * IdAlloc
	 * Tinyse 
	 * 
	 * @param string $service
	 * @return unknown_type
	 */
	public static function getKsService($service,$params=null){
		if( !array_key_exists( $service,  self::$ks_instance_map) ){
			return self::createKsService($service,$params);
		}
		return self::$ks_instance_map[$service];
	}
	
}