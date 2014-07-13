<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

/** @noinspection PhpUndefinedClassInspection */
abstract class SF_Action_Base extends Ap_Action_Abstract {

    private $_arrRequestData = null;

    protected $strFromApi;
    protected $strEncode;
    const ENCODE_UTF8 = 'utf-8';
    const ENCODE_GBK  = 'gbk';

    public function execute() {
        $arrRes = $this->__execute();
        $this->_adapteRetRes($arrRes);
        if ($this->_isRenderView()) {
            $this->_render($arrRes);
        } else {
            $this->_value($arrRes);
        }
    }


    /**
     * @desc 真正执行的部分，最后结果一定要return
     * @return mixed
     */
    abstract protected function __execute();

    /**
     * @desc 判断合适会渲染页面，合适只是返回值
     * @return bool
     */
    abstract function _isRenderView();

    /**
     * @desc 调整成用于Action返回的结构
     * @param $arrRes
     */
    protected function _adapteRetRes(&$arrRes)
    {
        $arrRes =  SF_Model_Page_Service_Base::adpatePSResultForAction($arrRes);
    }

    /**
     * @desc 当需要渲染页面时，实现
     * @param $arrRes
     */
    protected function _render($arrRes) {

    }

    /**
     * @desc 当只是需要返回值时实现
     * @param $arrRes
     */
    protected function _value($arrRes) {

    }

    /**
     * @desc 获取GET内容
     * @return array
     */
    protected function getGetData()
    {
        return $this->getDataFromRequestData('get');
    }

    /**
     * @desc 获取POST内容
     * @return array
     */
    protected function getPostData()
    {
        return $this->getDataFromRequestData('post');
    }

    /**
     * @param string $method HTTP_METHOD
     * @return array
     */
    private function getDataFromRequestData($method)
    {
        if (is_null($this->_arrRequestData))
        {
            $this->_arrRequestData = Saf_SmartMain::getCgi();
        }
        return $this->_arrRequestData[$method];
    }

    /**
     * @desc 将gbk编码转换成utf8
     * @param array|string $data
     * @return array|string
     */
    protected function _gbk2Utf8($data)
    {
        return SF_Utility_Manager::textProcessor()->convert($data, 'GBK', 'UTF-8');
    }

    /**
     * @desc 将utf8编码转换成gbk
     * @param array|string $data
     * @return array|string
     */
    protected function _utf82gbk($data)
    {
        return SF_Utility_Manager::textProcessor()->convert($data,'UTF-8', 'GBK');
    }

    /**
     * @return SF_Interface_ILog
     */
    protected function logger()
    {
        return SF_Log_Manager::getBDLogger(SF_Log_Manager::LOGGER_DEPTH);
    }

}
