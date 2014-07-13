<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Wrapper_CurlSF {

    const TIME_OUT=10;
    const GET_METHOD = 'GET';

    /**
     * @param string $url
     * @param array $queryData
     * @param array $header
     * @param int $timeOut
     * @return string
     */
    function get($url, $queryData , $header = array(), $timeOut = self::TIME_OUT )
    {
        return $this->_curl($url,$queryData, self::GET_METHOD, $timeOut,$header);
    }


    /**
     * @param string $url
     * @param array $postData
     * @param array $header
     * @param int $timeOut
     * @return string
     */
    function post($url, $postData , $header = array() , $timeOut = self::TIME_OUT)
    {
        return $this->_curl($url,$postData,'POST', $timeOut,$header);
    }

    /**
     * @param string $url
     * @param array $data
     * @param string $method
     * @param int $time_out
     * @param array $header
     * @return array|false
     */
    private function _curl($url, $data, $method,
                                $time_out, $header) {
        try {
            if (empty($url)) {
                return null;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (!empty($header))
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
            if (!empty($data)) {
                $query_string = http_build_query($data);
                if ($method === self::GET_METHOD) {
                    $url .= preg_match('/\\?$/i', $url) ? $query_string : ('?' . $query_string);
                } else {
                    // POST.
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                }
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            $ret = curl_exec($ch);
            curl_close($ch);
            return $ret === false ? null : $ret;
        } catch (Exception $e) {
            // Failed to perform a cURL session.
            return false;
        }
    }
} 