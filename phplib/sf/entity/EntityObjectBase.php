<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

abstract class SF_Entity_EntityObjectBase {

    /**
     * @param $name
     * @return mixed
     * @throws SF_Exception_InternalException
     */
    function __get($name)
    {
        $class= get_class($this);
        if (property_exists($class,$name))
        {
            return $this->_getPropertyValue($name);
        }
        else
        {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_GET_NOT_EXIST_PROS);
        }
    }

    /**
     * @param $name
     * @param $value
     */
    function __set($name, $value)
    {
        $class= get_class($this);
        if (property_exists($class,$name))
        {
           $this->_setPropertyValue($value, $name);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function _getPropertyValue($name)
    {
        return $this->$name;
    }

    /**
     * @param $value
     * @param $proName
     */
    protected function _setPropertyValue($value, $proName)
    {
        $this->$proName = $value;
    }


} 