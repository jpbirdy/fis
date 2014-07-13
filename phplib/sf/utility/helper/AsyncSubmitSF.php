<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Helper_AsyncSubmitSF extends SF_Utility_SingleInstanceSF {


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
     * @异步提交到队列
     *
     * @param int $cmdId 命令号，不超过15个英文字符
     * @param array $data 要发送的数据
     * @param string $product 产品线的名称
     * @param string $topic 使用的主题
     * @return string
     * @throws SF_Exception_AsyncSubmit
     */
    public function queue($cmdId, $data, $product, $topic)
    {
        Bd_Log::trace('NmqSubmitData'.'['.var_export(array(
                'cmdId' => $cmdId,
                'data' => $data,
            ), true).']');
        return self::submitNMQ($cmdId, $data, $product, $topic);
    }


    /**
     * @desc 提交到NMQ
     *
     * @param int $cmdId 命令号，不超过15个英文字符
     * @param array $data 要发送的数据
     * @param string $product 产品线的名称
     * @param string $topic 使用的主题
     * @return string
     * @throws SF_Exception_AsyncSubmit
     */
    private function submitNMQ($cmdId, $data, $product, $topic)
    {
        ral_set_logid(LOG_ID);
        $param = array(
            '_product' => $product, //产品线定义product，不要起过7个英文字符
            '_topic' => $topic, //产品线定义topic，不要起过7个英文字符
            '_cmd' => $cmdId, //产品线定义cmd，不要起过15个英文字符
            'data' => $data,
        );

        $ret = SF_Utility_Manager::requestor()->ral()->post('proxy-common', $param);

        Bd_Log::trace('NmqSubmitResult'.'['.var_export($ret, true).']');

        if (empty($ret)) {
            throw new SF_Exception_AsyncSubmit( '异步上报失败', array(
                'errno' => ral_get_errno(),
                'error_msg' => ral_get_error(),
                'protocol_status' => ral_get_protocol_code(),
                'retContent' => var_export($ret, true)
            ));
        }elseif ($ret['_error_no'] != 0) {
            throw new SF_Exception_AsyncSubmit( '异步上报异常', array(
                '_error_no' => $ret['_error_no'],
                '_error_msg' => $ret['_error_msg'],
                'err_no' => $ret['err_no'],
            ));
        }

        return $ret;
    }



}