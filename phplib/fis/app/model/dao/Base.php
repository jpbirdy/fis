<?php

/**
 * @desc 基础的DAO类，提供了简单的加载方法
 * @author
 */
abstract class Fis_App_Model_Dao_Base
{

    /**
     * @desc 调用CURL调用服务，仅UGC上传图片使用
     * @param $url
     * @param $param
     * @param $method
     * @return mixed
     * @throws Fis_App_Exception_AppException
     */
    public function callCurlService($url, $param, $method)
    {

        if (!function_exists('curl_init'))
        {
            throw new Fis_App_Exception_AppException('curl not found', Fis_App_Exception_ErrCodeMapping::SYS_ERR_CURL);
        }
        $ch = curl_init();
        if (false === $ch || !is_resource($ch))
        {
            throw new Fis_App_Exception_AppException('curl create failed', Fis_App_Exception_ErrCodeMapping::SYS_ERR_CURL);
        }
        if (empty($url))
        {
            throw new Fis_App_Exception_AppException('bad url given!', Fis_App_Exception_ErrCodeMapping::SYS_ERR_CURL);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $param = array_merge($param, array('method' => $method));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Fis_App');

        $ret = curl_exec($ch);
        if ($ret == false || $ret == null || trim($ret) == '')
        {
            Fis_Log::warning(__METHOD__ . 'curl post failed');
            throw new Fis_App_Exception_AppException('curl post failed', Fis_App_Exception_ErrCodeMapping::SYS_ERR_CURL);
        }
        $ret = json_decode($ret, true);

        if ($ret === false)
        {
            Fis_Log::warning(__METHOD__ . 'curl post failed');
            throw new Fis_App_Exception_AppException('curl post failed', Fis_App_Exception_ErrCodeMapping::SYS_ERR_CURL);
        }
        curl_close($ch);
        return $ret;
    }

}