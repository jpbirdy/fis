<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Helper_TextProcessorSF extends SF_Utility_SingleInstanceSF {


    /**
     * @desc 返回当前类的类名
     *
     * 内部代码如下
     * <code>
     *  return __CLASS__;
     * </code>
     *
     * @return string
     */
    static function getClass()
    {
       return __CLASS__;
    }


    /**
     * @param string|array $data 要转换的内容
     * @param $from 传入内容的编码
     * @param $to 要转成的编码
     *
     * @throws SF_Exception_InternalException
     * @return string|array
     */
    function convert( $data, $from = 'GBK', $to = 'UTF-8')
    {
        if (!is_array($data) && !is_string(strval($data)))
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::SYSTEM_METHOD_PARAM_ERROR,'convert 传入了错误的数据类型', array(
                'data' => $data
            ));
        }
        return Fis_String::iconv_recursive($data, $from, $to);
    }


    /**
     * @param string $str
     * @return bool
     * @throws SF_Exception_InternalException
     */
    function isGbk($str)
    {
        if (is_string($str))
        {
            return Fis_String::is_gbk($str);
        }
        else
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::SYSTEM_METHOD_PARAM_ERROR,'传入isGbk的参数不为array或string', array(
                'data' => $str
            ));
        }
    }


    function cutWords($str, $len, $dot = '...', $charset = 'utf8')
    {
        if (!is_string($str))
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::SYSTEM_METHOD_PARAM_ERROR,
                '传入'.__METHOD__.'的str参数不为string', array(
                'data' => $str
            ));
        }

        if (!is_numeric($len) && $len <= 0)
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::SYSTEM_METHOD_PARAM_ERROR,
                '传入'.__METHOD__.'的len参数不为数字或<=0', array(
                    'len' => $len
                ));
        }

        return Fis_String::cutString($str,$len-1,$dot,$charset);
    }
    


}