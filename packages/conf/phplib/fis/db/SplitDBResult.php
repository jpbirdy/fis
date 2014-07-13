<?php
/***************************************************************************
 *
*   Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
*
***************************************************************************/



/**
 * SplitDB
 * @author chenyuzhe <chenyuzhe01@baidu.com>
 * @since 2012-11-06
 * @package DB
 *
 */


// result of DB query
class Fis_Db_SplitDBResult extends Fis_Db_AbsDBResult
{
    private $_arrResult;
    private $_intCurResIndex;
    private $_intCurResPos;

    public function __construct()
    {
    	$this->_arrResult = array();
        $this->_intCurResIndex = 0;
        $this->_intCurResPos = 0;
    }

    public function __destruct()
    {
        if(count($this->_arrResult) !== 0)
        {
            $this->free();
        }
    }

	/**
	* @brief 获取当前行，并移动指针
	*
	* @param $type 获取方式
	*
	* @return 
	*/
    public function next($type = Fis_Db::FETCH_ASSOC)
    {
    	while ($this->_intCurResIndex < count($this->_arrResult))
    	{
    		if($type === Fis_Db::FETCH_ASSOC)
    		{
    			$row = $this->_arrResult[$this->_intCurResIndex]->fetch_assoc();
    		}
    		else
    		{
    			$row = $this->_arrResult[$this->_intCurResIndex]->fetch_row();
    		}
    		if($row)
    		{
    			$this->_intCurResPos++;
    			return $row;
    		}
    		// change to next res;
    		$this->_intCurResIndex++;
    		$this->_intCurResPos = 0;
    		if($this->_intCurResIndex < count($this->_arrResult))
    		{
    			$this->_arrResult[$this->_intCurResIndex]->data_seek(0);
    		}
    	}
    	
        return NULL;
    }

	/**
	* @brief 设置指针位置
	*
	* @param $where
	*
	* @return 
	*/
    public function seek($intPos)
    {
    	if($intPos < 0 || $intPos >= $this->count())
    	{
    		return false;
    	}
    	for($i=0;$i<count($this->_arrResult);$i++)
    	{
    		if($intPos < $this->_arrResult[$i]->num_rows)
    		{
    			if(!$this->_arrResult[$i]->data_seek($intPos))
    			{
    				return false;
    			}
    			$this->_intCurResIndex = $i;
    			$this->_intCurResPos = $intPos;
    			return true;
    		}
    		$intPos -= $this->_arrResult[$i]->num_rows;
    	}
    	
    	return false;
    }

	/**
	* @brief 获取指针位置
	*
	* @return 
	*/
    public function tell()
    {
    	$intPos = 0;
    	for($i=0;$i<$this->_intCurResIndex;$i++)
    	{
    		$intPos += $this->_arrResult[$i]->num_rows;
    	}
    	$intPos += $this->_intCurResPos;
        return $intPos;
    }

	/**
	* @brief 获取结果集行数
	*
	* @return 
	*/
    public function count()
    {
    	$intCount = 0;
    	foreach ($this->_arrResult as $result)
    	{
    		$intCount += $result->num_rows;
    	}
    		
        return $intCount;
    }

	/**
	* @brief 释放结果集资源
	*
	* @return 
	*/
    public function free()
    {
    	foreach ($this->_arrResult as $result)
    	{
        	$result->free();
    	}
    	$this->_arrResult = array();
    }
    
    /**
     * @brief 添加结果集
     *
     * @return
     */
    public function addresult(mysqli_result $result)
    {
    	//test NULl
    	if(count($this->_arrResult) === Fis_DB_SplitDB::MAX_SQL_SPLIT_COUNT)
    	{
    		return false;
    	}
    	$this->_arrResult[] = $result;
    	
    	return true;
    }
}
