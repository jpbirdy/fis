<?php
/**
 * @desc Bd_Timer的封装
 * @author liumingjun@baidu.com
 */

class SF_Wrapper_KSArch_Timer implements SF_Interface_ITimer
{

    private $_timer = null;

    /**
     * @desc 初始化计时器
     */
    function __construct()
    {
        $this->_timer = new Fis_Timer();
    }

    /**
     * @return bool
     */
    public function start()
    {
        return $this->_timer->start();
    }

    /**
     * @return int
     */
    public function stop()
    {
        return $this->_timer->stop();
    }

    /**
     * @return void
     */
    public function reset(){
        $this->_timer->reset();
    }

    /**
     * @return mixed
     */
    public function getTotalTime()
    {
        return $this->_timer->getTotalTime();
    }

    /**
     * @desc 获取累积时间
     * @return int
     */
    public function getTimeStamp()
    {
        return $this->_timer->getTimeStamp();
    }
}