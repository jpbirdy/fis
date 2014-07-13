<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */

class SF_Utility_Helper_DataContextSF extends SF_Utility_SingleInstanceSF {

    private $_snapShotBak = array();
    private $_snapShotBuffer = array();

    /**
     * @desc 将所有备份的SnapShot进行flush
     */
    public  function flushBakSnapShot()
    {
        self::flush($this->_snapShotBak);
    }

    /**
     * @desc 持久化数据
     * @param array $data
     */
    protected  function flush($data)
    {
        //TODO::实现flush方法，调用LogManager进行输出
    }

    /**
     * @desc 清空buffer
     */
    public  function resetBuffer()
    {
        $this->_snapShotBuffer = array();
    }

    /**
     * @desc 输出buffer中的SnapShot
     * @param bool $isBackup 是否要将当前flush的内容放到back中。
     */
    public  function flushSnapShot($isBackup = false)
    {
        if (!$isBackup)
        {
            $this->_snapShotBak = array_merge($this->_snapShotBak, $this->_snapShotBuffer);
        }
        self::flush($this->_snapShotBuffer);
        self::resetBuffer();
    }

    /**
     * @param array $data
     */
    public  function addSnapShot($data)
    {
        $this->_snapShotBuffer []= $data;
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