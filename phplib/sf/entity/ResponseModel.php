<?php
/**
 * @desc 当前app请求其他服务时，将返回对象化时用。
 * @author liumingjun@baidu.com
 *
 */

abstract class SF_Entity_ResponseModel extends SF_Entity_MappingSelfValidEntity implements ArrayAccess {
    /**
     * @desc 用于存放是否按原始键获取offset的内部变量
     * @var bool
     */
    private $_isOffsetByRawKey = false;


    /**
     * @desc
     * @param bool $isRawKey
     */
    public function setOffsetBy($isRawKey = false)
    {
        $this->_isOffsetByRawKey = $isRawKey;
    }

    /**
     * @return bool
     */
    public function isOffsetByRawKey()
    {
        return $this->_isOffsetByRawKey;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {

        $newOffset= $this->getRightOffset($offset);

        if ($newOffset != null)
        {
            if (!is_null($this->$newOffset))
            {
                return $this->$newOffset;
            }
        }
        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $newOffset= $this->getRightOffset($offset);

        if ($newOffset != null)
        {
            return  $this->$newOffset;
        }
        return null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $newOffset= $this->getRightOffset($offset);

        if ($newOffset != null)
        {
            $this->$newOffset = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $newOffset= $this->getRightOffset($offset);

        if ($newOffset != null)
        {
            $this->$newOffset = null;
        }
    }

    /**
     * @desc
     *
     * @param $offset
     * @return string|null 当存在时返回正确的等效offset，不存在时，返回null
     */
    private function getRightOffset($offset)
    {
        $newOffset = $offset;
        if ($this->isOffsetByRawKey()) {
            $fieldsProps = $this->getAllFieldsPros();
            $isKeyExist = array_key_exists($offset, $fieldsProps);
            if ($isKeyExist)
            {
                $newOffset = $fieldsProps[$offset];
            }
        }
        else {
            $isKeyExist = in_array($offset, $this->getAllPropName());
        }
        if($isKeyExist)
        {
            return $newOffset;
        }
        return null;

    }
}