<?php
/**
 * @desc 错误映射类
 * @notice 只有SF的错误码是4位及以下的，其他的app中定义的错误码必须是5位的，前2位是模块代号，后3位是错误代号
 * @author liumingjun@baidu.com
 */

class SF_Exception_ErrCodeMapping implements SF_Interface_ISupplyErrMsgCode {

    #region 可以根据需求覆写的方法
    /**
     * @desc 根据需求增加的映射关系，在子类中实现，当键名相同时会覆盖系统的映射。
     * @return array
     */
    protected function _getAdditionCodeMsgMapping()
    {
        return array();
    }

    /**
     * @desc 返回所属app的数字前缀
     * @return int
     */
    protected function _getPrefix()
    {
        return 0;
    }

    /**
     * @desc 不带前缀的错误码映射。当存在时候，抛出的异常码将是被映射的
     * @return array array('现在的错误码'=>'被映射的错误码')
     */
    protected function _getOldCodeMapping()
    {
        return array();
    }
    #endregion


    #region 不带前缀的错误码
    const SYSTEM_OK = 0;
    const SYS_ERR_UNKNOWN = 1;		//未知错误;
    const SYS_ERR_PARAM = 2;        //参数错误;
    const SYS_ERR_DB_FAILED = 3;	//统称数据库失败(连接、查询、更新等);

    const SYS_ERR_NOPERMISSION = 4;     //没有操作权限;
    const SYS_ERR_NOTLOGIN = 5;         //没有登录;
    const SYS_ERR_NETIO_FAILED = 6;     //网络IO失败;

    const SYS_ERR_SYSTEM_BUSY = 7;      //系统繁忙;
    const SYS_ERR_SIGN_FAILED = 8;      //签名计算失败;
    const SYS_ERR_DATA_WRONG	= 9;    //数据错误，比如数据库数据不一致;
    const SYS_ERR_LOCATION_FAIL	= 10;    //定位失败;
    const SYS_ERR_CITY_NOT_OPEN	= 11;    //城市未开，用于自有团，逐步开放城市;

    const SYS_ERR_KS_ERROR = 12;		//KS arch服务错误
    const SYS_ERR_INER_CALL = 13;		//内部调用失败;
    const SYS_ERR_INER_VERIFY = 14;		//内部条件校验失败;

    const SYS_ERR_READ_CONF_FAILED = 15; //读取配置文件失败;
    const SYS_ERR_NOT_INITIALIZED = 16; //未初始化;


    #region sf_system_error 10-xxx
    const SYSTEM_ERROR = 1000;
    const SYSTEM_DB_CREATE_CONNECTION_ERROR = 1001;
    const SYSTEM_METHOD_PARAM_ERROR = 1002;
    #endregion

    #region utility_error 20-xxx
    const UTILITY_VALIDATE_INIT_WRONG = 2001;
    const UTILITY_VALIDATE_WRONG = 2002;
    const UTILITY_VALIDATE_PASS_ERROR = 2003;
    const UTILITY_VALIDATE_WRONG_EXT_VALIDATOR = 2004;
    const UTILITY_VALIDATE_WRONG_VALIDATOR_TYPE = 2005;
    const UTILITY_HELPER_ASYNC_SUBMIT_ERROR = 2006;
    const UTILITY_HELPER_RUQUEST_SERVICE_ERROR = 2007;
    const UTILITY_MANGER_WRONG_SINGLE_INSTANCE = 2008;
    #endregion

    #region callservice_error  30-xxx
    const CALLSERVICE_WRONG_PS = 3001;
    const CALLSERVICE_WRONG_ACTION_NAME = 3002;
    const CALLSERVICE_NOT_REGISTER_ROUTER = 3003;
    const CALLSERVICE_ERROR_RETURN = 3004;
    const CALLSERVICE_INPUT_EMPTY_ERROR = 3005;
    const CALLSERVICE_WRONG_ROUTER = 3006;
    const CALLSERVICE_ABSENT_MODEL = 3007;
    #endregion

    #region entity_error  40-xxx
    const ENTITY_OBJECTLIZE_ERROR = 4001;
    const ENTITY_COLLECTION_CONSTRUCT_ERROR = 4002;
    const ENTITY_COLLECTION_TYPE_NOT_CONSIST = 4003;
    const ENTITY_WRONG_DATASOURCE_ENGINE = 4004;
    const ENTITY_PROS_FIELDS_MAPPING_ERROR = 4005;
    const ENTITY_PROS_FIELDS_NOT_MATCH = 4006;
    const ENTITY_GET_NOT_EXIST_PROS = 4007;
    #endregion

    #region datasource_error  50-xxx
    const DATASOURCE_EXECUTE_ERROR = 5001;
    const DATASOURCE_LOAD_ERROR = 5002;
    #endregion

    #region other_error  60-xxx
    const EXCEPTION_ERROR_MAPPING_WRONG = 6001;
    const ASPECT_FUNC_DEFINE_ERROR = 6002;
    const MODEL_DAO_NOT_EXIST = 6003;
    const MODEL_DAO_NOT_RIGHT = 6004;
    CONST SAF_RESPONSE_ERROR = 6005;

    #endregion

    #endregion

    /**
     * @param int $code 传入的是不带前缀的错误，就是那些const带的值
     * @return int
     */
    final public function getCodeWithPrefix($code)
    {
        $prefix = $this->_getPrefix();
        if (!empty($prefix))
        {
            if (array_key_exists($code, $this->_getMappingRelation()))
            {
                return $prefix . $code;
            }
        }
        return $code;
    }

    /**
     * @desc 会考虑对外部兼容的问题，判断依据根据_oldCodeMapping方法，返回的数组来。
     * @param int $code
     * @return int
     */
    final public function getDisplayCode($code)
    {
        $oldCodeMapping = $this->_getOldCodeMapping();

        if(!empty($oldCodeMapping) && array_key_exists($code, $oldCodeMapping))
        {
            $retCode = $oldCodeMapping[$code];
        }
        else if (array_key_exists($code, $this->_getMappingRelation()))
        {
            $retCode = $this->getCodeWithPrefix($code);
        }
        else
        {
            $retCode = $code;
        }

        return intval($retCode);
    }


    /**
     * @desc 系统默认的错误码映射表
     * @return array
     */
    private function _getCodeMsgMapping()
    {
        return array(
            self::SYSTEM_DB_CREATE_CONNECTION_ERROR => '数据库建立连接失败|系统异常请稍后重试！',
            self::SYSTEM_METHOD_PARAM_ERROR => '内部函数使用错误|系统异常请稍后重试！',
            self::CALLSERVICE_WRONG_PS => '调用CallService时请求了错误的PS|调用出错请检查!',
            self::CALLSERVICE_NOT_REGISTER_ROUTER => '没有注册CallService的Router|系统异常，请稍后重试!',
            self::CALLSERVICE_ERROR_RETURN => 'CallService调用出现异常，后端服务故障，详见日志|系统异常，请稍后重试!',
            self::CALLSERVICE_INPUT_EMPTY_ERROR => '未想callservice_dispatcher中传入参数|系统异常，请稍后重试!',
            self::CALLSERVICE_WRONG_ACTION_NAME => '未找到对应的CallServiceAction，请按规范添加。|系统异常，请稍后重试!',
            self::CALLSERVICE_WRONG_ROUTER => 'callservice的router不为SF_Interface_IRouteMapping的实例|系统异常，请稍后重试!',
            self::CALLSERVICE_ABSENT_MODEL => '缺少对应的Model，请检查|系统异常，请稍后重试!',
            self::UTILITY_VALIDATE_WRONG => '验证出错|传入内容有误，请检查！',
            self::UTILITY_VALIDATE_INIT_WRONG => '传入验证器的内容，格式错误，无法完成初始化|传入内容有误，请检查！',
            self::UTILITY_VALIDATE_PASS_ERROR => '创建验证链时的参数错误|系统异常,请稍后重试！',
            self::UTILITY_VALIDATE_WRONG_EXT_VALIDATOR => '传入了错误的外部验证器，请验证|系统异常,请稍后重试！',
            self::UTILITY_VALIDATE_WRONG_VALIDATOR_TYPE => '传入了错误的验证器，类型不是SF_Interface_IValidate，请检查|系统异常,请稍后重试！',
            self::UTILITY_HELPER_ASYNC_SUBMIT_ERROR => '异步提交系统出错，请排查|系统异常,请稍后重试！',
            self::UTILITY_HELPER_RUQUEST_SERVICE_ERROR=> '请求服务出错，请排查|系统异常,请稍后重试！',
            self::UTILITY_MANGER_WRONG_SINGLE_INSTANCE=> '要使用单例的类未继承SF_Utility_SingleInstanceSF|系统异常,请稍后重试！',
            self::ENTITY_OBJECTLIZE_ERROR => '对象化失败,请检查该PS的getRequestModelClassName方法是否正确书写|系统异常请稍后重试',
            self::ENTITY_COLLECTION_CONSTRUCT_ERROR => '实体的集合对象初始化失败|系统异常请稍后重试',
            self::EXCEPTION_ERROR_MAPPING_WRONG => '注入了错误的ErrCodeMapping对象,请检查|系统异常请稍后重试！',
            self::DATASOURCE_EXECUTE_ERROR => '数据源出错,请排查|系统异常请稍后重试！',
            self::ENTITY_PROS_FIELDS_MAPPING_ERROR => 'Entity的映射函数类型错误，需要为array|系统异常请稍后重试！',
            self::ENTITY_PROS_FIELDS_NOT_MATCH => '传入了未映射的原始字段|系统异常请稍后重试！',
            self::ENTITY_GET_NOT_EXIST_PROS => '请求了无效的属性|系统异常请稍后重试！',
            self::ENTITY_COLLECTION_TYPE_NOT_CONSIST => '传入实体集合的数据类型不一致|系统异常请稍后重试！',
            self::ENTITY_WRONG_DATASOURCE_ENGINE => '传入实体的dataSourceEngine没有继承自ICURDE接口|系统异常请稍后重试！',

            self::ASPECT_FUNC_DEFINE_ERROR => '切面方法定义错误，未实现IAspectFunc接口|系统异常请稍后重试！',
            self::MODEL_DAO_NOT_EXIST => '要初始化的Dao不存在|系统异常请稍后重试！',
            self::MODEL_DAO_NOT_RIGHT => 'DAO需要继承DaoBase类|系统异常请稍后重试！',
            self::SAF_RESPONSE_ERROR => 'SAF响应失败|系统异常请稍后重试！',

            #region 系统错误
            self::SYSTEM_ERROR => '数据库挂了|系统异常请重试！',
            self::SYS_ERR_UNKNOWN => '未知错误',
            self::SYS_ERR_PARAM => '参数错误',
            self::SYS_ERR_DB_FAILED => '统称数据库失败(连接、查询、更新等)',
            self::SYS_ERR_NOPERMISSION => '没有操作权限',
            self::SYS_ERR_NOTLOGIN => '没有登录',
            self::SYS_ERR_NETIO_FAILED => '网络IO失败',
            self::SYS_ERR_SYSTEM_BUSY => '系统繁忙',
            self::SYS_ERR_SIGN_FAILED => '签名计算失败',
            self::SYS_ERR_DATA_WRONG	=> '数据错误，比如数据库数据不一致',
            self::SYS_ERR_LOCATION_FAIL=> '定位失败',
            self::SYS_ERR_CITY_NOT_OPEN	=> '城市未开，用于自有团，逐步开放城市',
            self::SYS_ERR_KS_ERROR => 'KS arch服务错误',
            self::SYS_ERR_INER_CALL => '内部调用失败',
            self::SYS_ERR_INER_VERIFY => '内部条件校验失败',
            self::SYS_ERR_READ_CONF_FAILED => '读取配置文件失败',
            self::SYS_ERR_NOT_INITIALIZED=> '未初始化',
            #endregion

        );
    }

    /**
     * @return array
     */
    final private function _getMappingRelation()
    {
        $baseArr = $this->_getCodeMsgMapping();
        $addArr = $this->_getAdditionCodeMsgMapping();

        return $baseArr + $addArr;
    }

    /**
     * @param int $code
     * @return string
     */
    function getDisplayErrMsgByCode($code)
    {
        $retMsg = '';
        $codeMsgMapping = $this->_getMappingRelation();
        if (array_key_exists($code, $codeMsgMapping))
        {
            $retMsg = explode('|', $codeMsgMapping[$code]);
            $retMsg = end($retMsg);
        }

        return $retMsg;
    }

    /**
     * @param int $code
     * @return string
     */
    function getInternalErrMsgByCode($code)
    {
        $retMsg = '';
        $codeMsgMapping = $this->_getMappingRelation();

        if (array_key_exists($code, $codeMsgMapping))
        {
            $retMsg = explode('|', $codeMsgMapping[$code]);
            $retMsg = reset($retMsg);
        }

        return $retMsg;
    }
}