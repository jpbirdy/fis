<?php
/**
 * @desc ral的封装无需继承wrapper基类
 * @author liumingjun@baidu.com
 */

class SF_Wrapper_KSArch_RalSF {
    const RAL_POST = 'POST';
    const RAL_GET = 'GET';

    /**
     * @desc get请求时使用
     *
     * @param string $serviceName ral配置中的节点名
     * @param array $data 发送的数据内容
     * @param string $pathInfo eg:'/echo.php'
     * @return string
     */
    function get($serviceName, $data,$pathInfo = null)
    {
        return $this->ral($serviceName,$pathInfo,$data,self::RAL_GET);
    }

    /**
     * @desc get请求，希望直接将返回结果做jsondecode时使用
     *
     * @param string $serviceName ral配置中的节点名
     * @param array $data 发送的数据内容
     * @param string $pathInfo eg:'/echo.php'
     * @return string
     */
    function getArr($serviceName, $data,$pathInfo = null)
    {
        return $this->ral($serviceName,$pathInfo,$data,self::RAL_GET,'',true);
    }

    /**
     * @desc post请求时使用
     *
     * @param string $serviceName ral配置中的节点名
     * @param array $data 发送的数据内容
     * @param string $pathInfo eg:'/echo.php'
     * @param string $queryString eg:'usr=test' 仅当post时使用
     * @return string
     */
    function post($serviceName, $data,$pathInfo = null, $queryString = '')
    {
        return $this->ral($serviceName, $pathInfo,$data,self::RAL_POST, $queryString);
    }

    /**
     * @desc post请求，希望直接将返回结果做jsondecode时使用
     *
     * @param string $serviceName ral配置中的节点名
     * @param array $data 发送的数据内容
     * @param string $pathInfo eg:'/echo.php'
     * @return string
     */
    function postArr($serviceName, $data,$pathInfo = null)
    {
        return $this->ral($serviceName,$pathInfo,$data,self::RAL_POST,'',true);
    }

    /**
     * @desc ral查询的封装方法
     *
     * @param string $serviceName ral配置中的节点名
     * @param string $pathInfo eg:'/echo.php'
     * @param array $data 发送的数据内容
     * @param string $httpMethod post/get
     * @param string $queryString eg:'usr=test' 仅当post时使用
     * @param bool $jsonDecode 是否进行jsonDecode，并且会判错
     * @param int|string $logId 日志Id
     * @throws SF_Exception_RequestService
     * @return string|array
     */
    protected function ral($serviceName,$pathInfo,$data,$httpMethod, $queryString = '',$jsonDecode = false, $logId = LOG_ID)
    {
        ral_set_logid($logId);
        ral_set_pathinfo($pathInfo);
        $postParam = null;
        if ($httpMethod === self::RAL_POST)
        {
            ral_set_querystring($queryString);
            $postParam = $data;
        }
        else
        {
            $query = http_build_query($data);
            ral_set_querystring($query);
        }

        $result = ral($serviceName, $httpMethod, $postParam, rand());

        if (false === $result) {
            throw new SF_Exception_RequestService('ral服务异常，请求失败', array(
                'name' => $serviceName,
                'queryData' => $data,
                'pathInfo' => $pathInfo,
                'httpMethod' => $httpMethod,
                'queryString' => $queryString,
                'result' => $result,
                'ralMsg' => ral_get_error(),
                'ralErrno' => ral_get_errno(),
                'ralProtocolStatus' => ral_get_protocol_code(),
            ));
        }

        $ret = $result;

        if ($jsonDecode)
        {
            $ret = json_decode($result, true);

            if ($ret === false || $ret === NULL)
            {
                throw new SF_Exception_RequestService('ral服务获得的数据无法被jsondecode', array(
                    'name' => $serviceName,
                    'queryData' => $data,
                    'pathInfo' => $pathInfo,
                    'httpMethod' => $httpMethod,
                    'queryString' => $queryString,
                    'result' => $result,
                    'ralMsg' => ral_get_error(),
                    'ralErrno' => ral_get_errno(),
                    'ralProtocolStatus' => ral_get_protocol_code(),
                ));
            }
        }

        return $ret;
    }
} 