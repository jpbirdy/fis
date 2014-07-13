<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Entity_MappingEntityBase extends SF_Entity_EntityObjectBase {
    const WITHOUT_DATA_TOKEN = 1;

    /**
     * @var array 存放属性值的地方
     */
    private $_virtualProperties = array();

    /**
     * @var array 用于存放历史数据，用于对DB的update，只update变化的值
     */
    private $_originValues = array();


    /**
     * @var array 用于存放处理后的原始键和属性的映射关系
     */
    private $_kvedKeyPropMapping = array();

    private $_finishFirstInit = false;


    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if ($this->_checkPropertyExist($name))
        {
            if ( $this->_finishFirstInit  &&!array_key_exists($name, $this->_originValues) )
            {
                $this->_originValues[$name] = $this->$name;
            }

            $this->_setPropertyValue($value, $name);
        }
    }

    /**
     * @desc 响应empty的方法
     * @param $name
     * @return bool
     */
    function __isset($name)
    {
        if ($this->_checkPropertyExist($name))
        {
            return $this->_getPropertyValue($name);
        }
        else
        {
            return false;
        }

    }

    /**
     * @desc 响应unset的方法
     * @param $name
     */
    function __unset($name)
    {
        if ($this->_checkPropertyExist($name))
        {
            $this->_setPropertyValue(null,$name);

        }
    }


    /**
     * @param $name
     * @return mixed
     * @throws SF_Exception_InternalException
     */
    public function __get($name)
    {

        if ($this->_checkPropertyExist($name))
        {
            return $this->_getPropertyValue($name);
        }
        else
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_GET_NOT_EXIST_PROS, '',array(
                'get property' => $name
            ));
        }
    }

    /**
     * @desc 判断该实体是否为空
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_virtualProperties);
    }

    /**
     * @desc 判断是否未传入要初始化的data值
     * @param mixed $data
     * @return bool
     */
    protected function isWithoutData($data)
    {
        return $data === self::WITHOUT_DATA_TOKEN;
    }


    /**
     * @param array|int $data
     * @param bool $notCheckAllProsMatch
     */
    function __construct($data = self::WITHOUT_DATA_TOKEN, $notCheckAllProsMatch = true)
    {
        $this->_instantiate($data, $notCheckAllProsMatch);
    }

    /**
     * @desc 初始化整个对象
     *
     * @param array|int $data
     * @param bool $notCheckAllProsMatch 是否严格校验，需要传入的数组与映射的属性必须
     * @throws SF_Exception_InternalException
     */
    protected function _instantiate($data = self::WITHOUT_DATA_TOKEN, $notCheckAllProsMatch)
    {
        if (!$this->isWithoutData($data))
        {
            $this->clear();
            if( empty($data)
                || !is_array($data)
                || SF_Utility_Validate_ManagerSF::createValidChain($data,'instantiate entity data',false)->isIndexArray()->isPass())
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR,'初始化数据的格式错误', array(
                    'data' => $data
                ));
            }
            $this->_finishFirstInit = false;
            /** @var $data array */
            $this->_initEntity($data, $notCheckAllProsMatch);

            $this->_finishFirstInit = true;
        }
    }

    /**
     * @desc 判断是否存在该属性
     * @param $proName 在mapFieldsPros中定义的属性名，值列
     * @return bool
     */
    protected function _checkPropertyExist($proName)
    {
        return in_array($proName, $this->getAllPropName());
    }

    /**
     * @param $rawKey
     * @return bool
     */
    protected function _isRawKeyExist($rawKey)
    {
        return in_array($rawKey, $this->getAllFields());
    }

    /**
     * @desc 返回键值对化array，键为字段和属性数组
     * @throws SF_Exception_InternalException
     * @return array
     */
    protected function getAllFieldsPros()
    {
        $fieldsProsMapping = $this->mapFieldsPros();
        if (!is_array($fieldsProsMapping))
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_PROS_FIELDS_MAPPING_ERROR);
        }
        if (empty($this->_kvedKeyPropMapping))
        {
            foreach($fieldsProsMapping as $rawKey => $prop)
            {
                if (is_numeric($rawKey))
                {
                    $rawKey = $prop;
                }
                $this->_kvedKeyPropMapping [$rawKey] = $prop;
            }
        }

        return $this->_kvedKeyPropMapping;
    }

    /**
     * @return array 索引数组
     */
    public function getAllFields()
    {
        return array_keys($this->getAllFieldsPros());
    }

    /**
     * @Desc 返回所有
     * @return array 索引数组
     */
    public function getAllPropName()
    {
        return array_values( $this->getAllFieldsPros());
    }

    /**
     * @param array $rawData
     * @param bool $notCheckAllProsMatch
     *
     * @throws SF_Exception_InternalException
     */
    private function _initEntity($rawData, $notCheckAllProsMatch = false)
    {
        $fieldsProsMapping = $this->getAllFieldsPros();

        foreach($rawData as $rawKey => $value)
        {
            if (!$this->_isRawKeyExist($rawKey) && !$notCheckAllProsMatch)
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_PROS_FIELDS_NOT_MATCH);
            }
            else if($this->_isRawKeyExist($rawKey)){
                $proName = $fieldsProsMapping[$rawKey];
                $this->$proName = $value;
            }
        }
    }

    /**
     * @desc 返回对应的映射数组
     * @param bool $isRaw 如果等于true表示以原始键为键名，如果为false则以属性键为键名。
     * @return array
     */
    public function transToArr($isRaw = true)
    {
        $retArr = array();

        $fieldsProsMapping = $this->getAllFieldsPros();
        foreach($fieldsProsMapping as $field => $property)
        {
            $key = $field;
            if ($property !== null)
            {
                if (!$isRaw)
                {
                    $key = $property;
                }
                $retArr[$key] = $this->$property;
            }
        }
        $this->_afterToArray($retArr);
        return $retArr;
    }

    /**
     * @abstract
     * @desc 在transToArr之后运行的函数，用于调整函数transToArr返回的数组值
     * @notice 一定要使用$arr来调整，不可以在其中使用$this
     * @param array $arr transToArr出来的数组
     */
    abstract protected function _afterToArray(&$arr);

    /**
     * @desc 获取变更过的数据
     * @param bool $isRaw 控制返回的键名是属性键还是原始键，当true代表是原始键
     * @return array
     */
    public function getChangedData($isRaw = true)
    {
        if (empty($this->_originValues))
        {
            return array();
        }

        $newData = $this->transToArr(false);
        $updatedData = $this->_arrayRecursiveDiff($this->_originValues, $newData);

        if ($isRaw)
        {
            $updatedData = $this->_getArrayWithRawKey($updatedData);
        }

        return $updatedData;
    }

    /**
     * @desc 请勿在外层使用该方法，如果要使用，请使用getAllFieldsPros方法
     * @see  getAllFieldsPros
     * @return array 属性和字段的映射关系 array(‘原始字段'=>’属性','原始字段/属性相同');
     */
    abstract protected function mapFieldsPros();

    /**
     * @param $value
     * @param $proName
     */
    protected function _setPropertyValue($value, $proName)
    {
        $this->_virtualProperties[$proName] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function _getPropertyValue($name)
    {
        return $this->_virtualProperties[$name];
    }


    /**
     * @desc 清空实体的内容
     */
    public function clear()
    {
        $this->_virtualProperties = array();
        $this->_originValues = array();
    }

    /**
     * @param array $data 要merge到该对象中的值
     * @return $this
     */
    public function merge($data)
    {
        $this->_initEntity($data, true);
        return $this;
    }

    /**
     * @param $arrOld
     * @param $arrNew
     * @return array
     */
    protected function _arrayRecursiveDiff($arrOld, $arrNew)
    {
        $diff = array();

        foreach ($arrOld as $key => $value)
        {
            if ($value != $arrNew[$key])
            {
                $diff[$key] = $arrNew[$key];
            }
        }

        return $diff;
    }

    /**
     * @param $data
     * @return array
     */
    protected function _getArrayWithRawKey($data)
    {
        $prosFieldsMapping = array_flip($this->getAllFieldsPros());

        $newUpdatedData = array();
        foreach ($data as $propName => $value) {
            $rawKey = $prosFieldsMapping[$propName];
            $newUpdatedData[$rawKey] = $value;
        }

        return $newUpdatedData;
    }
}
