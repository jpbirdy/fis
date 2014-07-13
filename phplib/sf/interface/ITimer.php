<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
interface SF_Interface_ITimer
{
    /**
     * @return void
     */
    public function reset();

    /**
     * @desc 获取累积时间
     * @return int
     */
    public function getTimeStamp();

    /**
     * @return bool
     */
    public function start();

    /**
     * @return mixed
     */
    public function getTotalTime();

    /**
     * @return int
     */
    public function stop();
}