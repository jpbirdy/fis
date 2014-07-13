<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
class SF_CallService_Dispatcher implements SF_Interface_IGetClassName {


    private $_param = null;
    const SERVICE_PAGE_PREFIX = 'Service_Page_';

    /**
     * @param null $param
     */
    function __construct($param = null)
    {
       $this->_param = $param;
    }


    /**
     * @return array 返回路由配置
     * @throws SF_Exception_InternalException
     */
    protected function getRouter()
    {
        $curAppName = SF_Utility_Manager::configuration()->getCurrApp();
        $fileName = 'CallServiceRouter';
        $routerClass = ucfirst($curAppName) . '_'. $fileName;
        //saf调用跨应用的时候才会走到
        if (!class_exists($routerClass))
        {
            /** @noinspection PhpUndefinedClassInspection */
            Ap_Loader::getInstance()->registerLocalNamespace(ucfirst($curAppName));
        }

        $router = new $routerClass();

        if (!$router instanceof SF_Interface_IRouteMapping)
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::CALLSERVICE_INPUT_EMPTY_ERROR);
        }

        $ret = $router->getRouteMapping();

        if (empty($ret))
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::CALLSERVICE_NOT_REGISTER_ROUTER);
        }

        return $ret;
    }

    /**
     * @param array $param
     * @throws SF_Exception_InternalException
     * @return mixed
     */
    public function execute($param = null){

        if (is_null($param))
        {
            if (is_null($this->_param))
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::CALLSERVICE_INPUT_EMPTY_ERROR);
            }
            $param = $this->_param;
        }

        $reqModel = new SF_Entity_CallServiceRequestModel($param);

        $psClassName = $this->getActualRequestPS($reqModel->psSeg);
        if (class_exists($psClassName))
        {
            /**
             * @var SF_Model_Page_Service_Base $ps
             */
            $ps = new $psClassName();

            $data = $reqModel->data;

            if (!is_object($data) && isset($data['arrReq']))
            {
                $data = $data['arrReq'];
            }

            $ret = $ps->execute($data);
            return $ret;
        }
        else
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::CALLSERVICE_WRONG_PS,'', array(
                'psClassName' => $psClassName,
                'reqModel' => $reqModel->transToArr(),
                'mapping' => $this->getRouter()
            ));

        }
    }

     /**
     * @desc 获取要调取的那个PS名称
     *
     * @param string $psSeg 请求的ps主体名
     * @return string
     */
    protected function getActualRequestPS($psSeg)
    {
        $mapping = $this->getRouter();
        $ret = $psSeg;
        if (array_key_exists($psSeg, $mapping))
        {
            $ret = $mapping[$psSeg];
        }
        $ret = self::SERVICE_PAGE_PREFIX .$ret;
        return $ret;
    }

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
}