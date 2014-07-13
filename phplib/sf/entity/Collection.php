<?php
/**
 * @desc 该集合类只能存放同一种的数据类型，比如纯CouponEntity，那么整个集合只能存CouponEntity，不能出现其他的类型
 * @author liumingjun@baidu.com
 */

final class SF_Entity_Collection extends ArrayObject {

    private $_entryClassName = null;

    /**
     * @desc Construct a new array object
     * @param array $input The input parameter accepts an array or an Object.
     * @throws SF_Exception_InternalException
     */
    function __construct($input = null)
    {
        if(!is_null($input) && !is_array($input))
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR, '输入参数需要为array', array(
                'input' => $input
            ));
        }
        if (is_null($input))
        {
            $input = array();
        }

        parent::__construct($input, ArrayObject::ARRAY_AS_PROPS);
        $this->_setEntryClassName();
        if(count($input) > 0)
        {
            foreach($input as $index => $entry)
            {
                if (!is_object($entry))
                {
                    throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR, '数组中的元素需要为对象',array(
                        'index' => $index,
                        'entry' => $entry,
                    ) );
                }
                $this->_checkTypeConsistency(get_class($entry));
            }
        }

    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     *
     * Appends object
     *
     * @param SF_Entity_EntityObjectBase $object <p>
     * The value being appended.
     * @return void
     */
    public function append($object)
    {
        $this->_checkTypeConsistency(get_class($object));
        parent::append($object);
        $this->_setEntryClassName();
    }


    /**
     * @desc 用于检查集合中的一致性。也就是说一个new出一个collection只能存放一种类型。
     */
    private function _checkTypeConsistency($className)
    {
        $entryType = $this->getType();
        if (!is_null($entryType))
        {
            if (trim($className) != trim($entryType))
            {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR, '数组中的元素需要为对象',array(
                    'ori' => $entryType,
                    'new' => $className
                ));
            }
        }
    }


    /**
     * @desc 获取当前对象的类型
     * @return string
     */
    public function getType()
    {
        if($this->count() === 0 && is_null($this->_entryClassName))
        {
            return null;
        }
        else
        {
            $this->_setEntryClassName();
            return $this->_entryClassName;
        }
    }

    /**
     * @desc 返回该collection对象是否为空
     * @return bool
     */
    public function isEmpty()
    {
        $arr = (array)$this;
        return empty($arr);
    }

    private function _setEntryClassName()
    {
        if ($this->count() !== 0 && is_null($this->_entryClassName))
        {
            $className = get_class(current($this));
            $this->_entryClassName = $className;
        }
    }


    /**
     * @desc 将该collection的内容转化成数组
     * @param bool $isRecursive 是否递归转换，默认只转换一层。
     * @return array
     */
    public function toArray($isRecursive = false)
    {
        if (!$isRecursive)
        {
            $retData = $this->getArrayCopy();
        }
        else
        {
            $retData = array();
            if (!$this->isEmpty())
            {
                /**
                 * @var SF_Entity_MappingEntityBase $entry
                 */
                foreach ($this as $key => $entry) {
                    $retData[$key] = $entry->transToArr();
                }
            }
        }
        return $retData;
    }
}