<?php
/**
 * @file Conf.php
 * @author liaohuiqin01
 * @date 2013/05/11 20:54:49
 * @brief 支付跨APP读取配置;
 *		  默认读取self-tg下的配置;	
 */

class SF_Wrapper_KSArch_ConfSF{
    /**
     * @brief  获取配置项内容;
     *
     * @param string $path 配置路径
     * @param string $app APP
     * @return bool|mixed|null|string 配置项内容;
     */
	public static function getConf($path,$app='tradecenter'){
		$conf = '';
		$curApp = Fis_Appenv::getCurrApp();
		if(!empty($curApp)){//当前app
			if($app == $curApp){ //无须切换
				 $conf = Bd_Conf::getAppConf($path);
			}else{
				//先切换APP;
				Fis_Appenv::setCurrApp($app);
				$conf = Bd_Conf::getAppConf($path);
				//切换回当前app;
				Fis_Appenv::setCurrApp($curApp);
			}
		}
		if($conf==false){
			Bd_Log::debug('Bd_Conf path('.$path.') app('.$app.') get conf failed');
			return '';
		}
		return $conf;
	}

    public static function getCurrApp()
    {
        return Fis_Appenv::getCurrApp();
    }
}

